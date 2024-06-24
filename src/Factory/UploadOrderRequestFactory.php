<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class UploadOrderRequestFactory implements UploadOrderRequestFactoryInterface
{
    public function __construct(
        /** @var FactoryInterface<UploadOrderRequestInterface> $decoratedFactory */
        private readonly FactoryInterface $decoratedFactory,
    ) {
    }

    /** @psalm-suppress MoreSpecificReturnType */
    public function createNew(): UploadOrderRequestInterface
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return $this->decoratedFactory->createNew();
    }
}
