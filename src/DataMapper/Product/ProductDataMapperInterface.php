<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper\Product;

use Setono\PeakWMS\DataTransferObject\Product\Product;
use Setono\SyliusPeakPlugin\Model\ProductVariantInterface;

interface ProductDataMapperInterface
{
    /**
     * Maps a product variant to a Peak WMS product
     */
    public function map(ProductVariantInterface $productVariant, Product $product): void;
}
