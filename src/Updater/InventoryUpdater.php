<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Updater;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\Product\Product;
use Setono\PeakWMS\Request\Query\Product\PageQuery;
use Setono\SyliusPeakPlugin\Model\InventoryUpdateInterface;
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
            ->filter(fn (Product $product) => $product->variantId === $variantCode)
        ;

        if (count($collection) !== 1) {
            throw new \RuntimeException(sprintf(
                'The product with id %s either does not have a variant with id/code %s or has multiple products with the same variant id/code',
                $productCode,
                $variantCode,
            ));
        }

        $this->map($collection[0], $productVariant);

        $this->getManager($productVariant)->flush();
    }

    // todo what happens if the transitions are not possible?
    public function updateAll(bool $onlyUpdated = true): void
    {
        $inventoryUpdate = $this->inventoryUpdateProvider->getInventoryUpdate();
        $this->getManager($inventoryUpdate)->persist($inventoryUpdate);

        if (!$this->inventoryUpdateWorkflow->can($inventoryUpdate, InventoryUpdateWorkflow::TRANSITION_RESET)) {
            throw new \RuntimeException('The inventory update cannot be reset');
        }

        try {
            $this->transition($inventoryUpdate, InventoryUpdateWorkflow::TRANSITION_RESET);
            $this->transition($inventoryUpdate, InventoryUpdateWorkflow::TRANSITION_PROCESS);

            $manager = $this->getManager(ProductVariant::class);
            $productVariantRepository = $this->getRepository(ProductVariant::class);

            $i = 0;
            $products = $this->client->product()->iterate(PageQuery::create(updatedAfter: $inventoryUpdate->getNextUpdateThreshold()));
            foreach ($products as $product) {
                ++$i;

                if ($i % 100 === 0) {
                    $manager->flush();
                    $manager->clear();

                    $inventoryUpdate->setProductsProcessed($i);
                }

                try {
                    Assert::notNull($product->variantId, sprintf(
                        'Product with id %d does not have a variant id. It is expected that Peak WMS has the same structure of products as Sylius, namely that all products at least have one variant.',
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

                    $this->map($product, $productVariant);
                } catch (\Throwable $e) {
                    $inventoryUpdate->addError($e->getMessage());
                }
            }

            $inventoryUpdate->setProductsProcessed($i);
            $manager->flush();

            $this->transition($inventoryUpdate, InventoryUpdateWorkflow::TRANSITION_COMPLETE);
        } catch (\Throwable $e) {
            $inventoryUpdate->addError($e->getMessage());
            $this->transition($inventoryUpdate, InventoryUpdateWorkflow::TRANSITION_FAIL);
        } finally {
            $this->getManager($inventoryUpdate)->flush();
        }
    }

    private function transition(InventoryUpdateInterface $inventoryUpdate, string $transition): void
    {
        $this->inventoryUpdateWorkflow->apply($inventoryUpdate, $transition);

        $this->getManager($inventoryUpdate)->flush();
    }

    private function map(Product $product, ProductVariantInterface $productVariant): void
    {
        $productVariant->setOnHand((int) $product->availableToSell + (int) $productVariant->getOnHold());
    }
}
