<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Event;

use Setono\PeakWMS\DataTransferObject\Product\Product;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class ProductMappedEvent
{
    public function __construct(
        public readonly Product $product,
        public readonly ProductVariantInterface $productVariant,
    ) {
    }
}
