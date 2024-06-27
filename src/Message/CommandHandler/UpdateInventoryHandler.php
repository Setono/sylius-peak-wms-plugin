<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\CommandHandler;

use Setono\SyliusPeakPlugin\Message\Command\UpdateInventory;
use Setono\SyliusPeakPlugin\Updater\InventoryUpdaterInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final class UpdateInventoryHandler
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly InventoryUpdaterInterface $inventoryUpdater,
    ) {
    }

    public function __invoke(UpdateInventory $message): void
    {
        if (null === $message->productVariant) {
            $this->inventoryUpdater->updateAll();

            return;
        }

        $productVariant = $this->productVariantRepository->find($message->productVariant);
        if (!$productVariant instanceof ProductVariantInterface) {
            throw new UnrecoverableMessageHandlingException(sprintf('Product variant with id %d does not exist', $message->productVariant));
        }

        $this->inventoryUpdater->update($productVariant);
    }
}
