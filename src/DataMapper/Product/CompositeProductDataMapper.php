<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper\Product;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\CompositeCompilerPass\CompositeService;
use Setono\PeakWMS\DataTransferObject\Product\Product;
use Setono\SyliusPeakPlugin\Event\ProductMappedEvent;
use Setono\SyliusPeakPlugin\Model\ProductVariantInterface;

/**
 * @extends CompositeService<ProductDataMapperInterface>
 */
final class CompositeProductDataMapper extends CompositeService implements ProductDataMapperInterface
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function map(ProductVariantInterface $productVariant, Product $product): void
    {
        foreach ($this->services as $service) {
            $service->map($productVariant, $product);
        }

        $this->eventDispatcher->dispatch(new ProductMappedEvent($product, $productVariant));
    }
}
