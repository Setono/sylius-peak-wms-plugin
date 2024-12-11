<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Updater;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\Product\Product;
use Setono\PeakWMS\Request\Query\KeySetPageQuery;
use Setono\SyliusPeakPlugin\Provider\InventoryUpdateProviderInterface;
use Setono\SyliusPeakPlugin\Workflow\InventoryUpdateWorkflow;
use Sylius\Component\Core\Model\ProductVariant;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Webmozart\Assert\Assert;

final class InventoryUpdater implements InventoryUpdaterInterface
{
    use ORMTrait;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly InventoryUpdateProviderInterface $inventoryUpdateProvider,
        private readonly WorkflowInterface $inventoryUpdateWorkflow,
        ManagerRegistry $managerRegistry,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function update(ProductVariantInterface $productVariant): void
    {
        $productCode = $productVariant->getProduct()?->getCode();
        $variantCode = $productVariant->getCode();

        if (null === $productCode || null === $variantCode) {
            throw new \RuntimeException(sprintf(
                'Product variant with id %d does not have a product code or variant code',
                (int) $productVariant->getId(),
            ));
        }

        $collection = $this
            ->client
            ->product()
            ->getByProductId($productCode)
            ->filter(fn (Product $product): bool => $product->variantId === $variantCode)
        ;

        Assert::count($collection, 1, sprintf(
            'Expected to find exactly one product variant with code %s, but found %d',
            $variantCode,
            count($collection),
        ));

        $this->updateOnHand((int) $collection[0]->availableToSell, $productVariant);

        $this->getManager($productVariant)->flush();
    }

    public function updateAll(bool $onlyUpdated = true): void
    {
        $inventoryUpdate = $this->inventoryUpdateProvider->getInventoryUpdate();
        $manager = $this->getManager($inventoryUpdate);
        $manager->persist($inventoryUpdate);
        $manager->flush();

        try {
            $this->inventoryUpdateTransition(InventoryUpdateWorkflow::TRANSITION_RESET);
            $this->inventoryUpdateTransition(InventoryUpdateWorkflow::TRANSITION_PROCESS);

            $productVariantRepository = $this->getRepository(ProductVariant::class);

            $i = 0;
            $products = $this->client->product()->iterate(KeySetPageQuery::create());
            foreach ($products as $product) {
                ++$i;

                if ($i % 100 === 0) {
                    $inventoryUpdate->setProductsProcessed($i);

                    $manager->flush();
                    $manager->clear();

                    // We need to get the inventory update again because the previous one is detached
                    $inventoryUpdate = $this->inventoryUpdateProvider->getInventoryUpdate();
                }

                try {
                    Assert::notNull($product->variantId, sprintf(
                        'Product with id %d does not have a variant id.',
                        (int) $product->id,
                    ));

                    $productVariant = $productVariantRepository->findOneBy(['code' => $product->variantId]);
                    Assert::notNull(
                        $productVariant,
                        sprintf('Product variant with code %s does not exist', $product->variantId),
                    );

                    if ($product->orderedByCustomers !== $productVariant->getOnHold()) {
                        $inventoryUpdate->addWarning(sprintf(
                            'Product variant with code %s has %d on hold in Sylius and %d on hold in Peak WMS',
                            $product->variantId,
                            (int) $productVariant->getOnHold(),
                            (int) $product->orderedByCustomers,
                        ));
                    }

                    $this->updateOnHand((int) $product->availableToSell, $productVariant);
                } catch (\Throwable $e) {
                    $inventoryUpdate->addError($e->getMessage());
                }
            }

            $inventoryUpdate->setProductsProcessed($i);
            $manager->flush();

            $this->inventoryUpdateTransition(InventoryUpdateWorkflow::TRANSITION_COMPLETE);
        } catch (\Throwable $e) {
            $inventoryUpdate->addError($e->getMessage());
            $this->inventoryUpdateTransition(InventoryUpdateWorkflow::TRANSITION_FAIL);
        } finally {
            $manager->flush();
            $manager->clear();
        }
    }

    private function inventoryUpdateTransition(string $transition): void
    {
        $inventoryUpdate = $this->inventoryUpdateProvider->getInventoryUpdate();
        $this->inventoryUpdateWorkflow->apply($inventoryUpdate, $transition);

        $this->getManager($inventoryUpdate)->flush();
    }

    private function updateOnHand(int $quantity, ProductVariantInterface $productVariant): void
    {
        $productVariant->setOnHand($quantity + (int) $productVariant->getOnHold());
    }
}
