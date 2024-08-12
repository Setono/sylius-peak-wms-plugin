<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\SyliusPeakPlugin\Model\WebhookRegistrationInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class WebhookRegistrationFactory implements WebhookRegistrationFactoryInterface
{
    public function __construct(
        /** @var FactoryInterface<WebhookRegistrationInterface> $decoratedFactory */
        private readonly FactoryInterface $decoratedFactory,
    ) {
    }

    /** @psalm-suppress MoreSpecificReturnType */
    public function createNew(): WebhookRegistrationInterface
    {
        /** @psalm-suppress LessSpecificReturnStatement */
        return $this->decoratedFactory->createNew();
    }

    public function createFromData(string $version, array $webhooks): WebhookRegistrationInterface
    {
        $obj = $this->createNew();
        $obj->setVersion($version);
        $obj->setWebhooks($webhooks);

        return $obj;
    }
}
