<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Updater;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\Stock\Stock;
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
            ->stock()
            ->getByProductId($productCode, $variantCode)
        ;

        $this->updateOnHand((int) $collection->sum(fn (Stock $stock): int => (int) $stock->quantity), $productVariant);

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
            $stock = $this->client->stock()->iterate(KeySetPageQuery::create());
            foreach ($stock as $item) {
                ++$i;

                if ($i % 100 === 0) {
                    $inventoryUpdate->setProductsProcessed($i);

                    $manager->flush();
                    $manager->clear();

                    // We need to get the inventory update again because the previous one is detached
                    $inventoryUpdate = $this->inventoryUpdateProvider->getInventoryUpdate();
                }

                try {
                    Assert::notNull($item->variantId, sprintf(
                        'Stock with id %d does not have a variant id.',
                        (int) $item->id,
                    ));

                    $productVariant = $productVariantRepository->findOneBy(['code' => $item->variantId]);
                    Assert::notNull(
                        $productVariant,
                        sprintf('Product variant with code %s does not exist', $item->variantId),
                    );

                    if ($item->reservedQuantity !== $productVariant->getOnHold()) {
                        $inventoryUpdate->addWarning(sprintf(
                            'Product variant with code %s has %d on hold in Sylius and %d on hold in Peak WMS',
                            $item->variantId,
                            (int) $productVariant->getOnHold(),
                            (int) $item->reservedQuantity,
                        ));
                    }

                    $this->updateOnHand((int) $item->quantity, $productVariant);
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
