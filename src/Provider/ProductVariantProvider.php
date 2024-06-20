<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataStockAdjust;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Webmozart\Assert\Assert;

final class ProductVariantProvider implements ProductVariantProviderInterface
{
    use ORMTrait;

    public function __construct(
        ManagerRegistry $managerRegistry,
        /** @var class-string<ProductVariantInterface> $productVariantClass */
        private readonly string $productVariantClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function provideFromStockAdjustment(WebhookDataStockAdjust $stockAdjustment): ?ProductVariantInterface
    {
        $obj = $this
            ->getRepository($this->productVariantClass)
            ->createQueryBuilder('o')
            ->where('o.code = :code')
            ->setParameter('code', $stockAdjustment->variantId)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        Assert::nullOrIsInstanceOf($obj, ProductVariantInterface::class);

        return $obj;
    }
}
