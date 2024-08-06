<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class UploadProductVariantRequestFactory implements UploadProductVariantRequestFactoryInterface
{
    public function __construct(
        /** @var FactoryInterface<UploadProductVariantRequestInterface> $decoratedFactory */
        private readonly FactoryInterface $decoratedFactory,
    ) {
    }

    /** @psalm-suppress MoreSpecificReturnType */
    public function createNew(): UploadProductVariantRequestInterface
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return $this->decoratedFactory->createNew();
    }
}
