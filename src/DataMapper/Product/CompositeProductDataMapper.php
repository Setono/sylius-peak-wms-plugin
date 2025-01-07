<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper\Product;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\CompositeCompilerPass\CompositeService;
use Setono\PeakWMS\DataTransferObject\Product\Product;
use Setono\SyliusPeakPlugin\Event\ProductMappedEvent;
use Sylius\Component\Core\Model\ProductVariantInterface;

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

        // todo not sure this line belongs here, but it is convenient though
        $this->eventDispatcher->dispatch(new ProductMappedEvent($product, $productVariant));
    }
}
