<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\SyliusPeakPlugin\Model\RegisteredWebhooksInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class RegisteredWebhooksFactory implements RegisteredWebhooksFactoryInterface
{
    public function __construct(
        /** @var FactoryInterface<RegisteredWebhooksInterface> $decoratedFactory */
        private readonly FactoryInterface $decoratedFactory,
    ) {
    }

    /** @psalm-suppress MoreSpecificReturnType */
    public function createNew(): RegisteredWebhooksInterface
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return $this->decoratedFactory->createNew();
    }

    public function createFromData(string $version, array $webhooks): RegisteredWebhooksInterface
    {
        $obj = $this->createNew();
        $obj->setVersion($version);
        $obj->setWebhooks($webhooks);

        return $obj;
    }
}
