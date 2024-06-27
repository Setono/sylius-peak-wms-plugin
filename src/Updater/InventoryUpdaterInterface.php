<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Updater;

use Sylius\Component\Core\Model\ProductVariantInterface;

interface InventoryUpdaterInterface
{
    /**
     * Will update the inventory for a single product variant
     */
    public function update(ProductVariantInterface $productVariant): void;

    /**
     * Will update the inventory for all product variants
     *
     * @param bool $onlyUpdated If true, only the product variants that have been updated since the last update will be updated
     */
    public function updateAll(bool $onlyUpdated = true): void;
}
