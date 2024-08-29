<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper\Product;

use Setono\PeakWMS\DataTransferObject\Product\Product;
use Setono\SyliusPeakPlugin\Model\ProductVariantInterface;

final class ProductDataMapper implements ProductDataMapperInterface
{
    public function map(ProductVariantInterface $productVariant, Product $product): void
    {
        $product->productId = $productVariant->getProduct()?->getCode();
        $product->variantId = $productVariant->getCode();
        $product->itemNumber = $productVariant->getCode();
        $product->description = $productVariant->getProduct()?->getName();
    }
}
