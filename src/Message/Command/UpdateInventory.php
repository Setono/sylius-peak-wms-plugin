<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\Command;

use Sylius\Component\Core\Model\ProductVariantInterface;

/**
 * Will update the inventory for a product variant
 */
final class UpdateInventory implements CommandInterface
{
    public int $productVariant;

    public function __construct(int|ProductVariantInterface $productVariant)
    {
        if ($productVariant instanceof ProductVariantInterface) {
            $productVariant = (int) $productVariant->getId();
        }

        $this->productVariant = $productVariant;
    }
}
