<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper\Product;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Setono\PeakWMS\DataTransferObject\Product\Product;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Model\ProductImageInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Webmozart\Assert\Assert;

final class ImageProductDataMapper implements ProductDataMapperInterface
{
    public function __construct(
        private readonly CacheManager $cacheManager,
        private readonly string $filter = 'sylius_shop_product_large_thumbnail',
    ) {
    }

    public function map(ProductVariantInterface $productVariant, Product $product): void
    {
        $image = self::getImagePath($productVariant);
        if (null === $image) {
            return;
        }

        $product->imagePath = $this->cacheManager->getBrowserPath(
            path: $image,
            filter: $this->filter,
        );
    }

    private static function getImagePath(ProductVariantInterface $productVariant): ?string
    {
        $image = $productVariant->getImages()->first();
        if ($image instanceof ImageInterface) {
            return $image->getPath();
        }

        $product = $productVariant->getProduct();
        Assert::isInstanceOf($product, ProductInterface::class);

        $image = $product->getImages()->first();
        if ($image instanceof ProductImageInterface) {
            return $image->getPath();
        }

        return null;
    }
}
