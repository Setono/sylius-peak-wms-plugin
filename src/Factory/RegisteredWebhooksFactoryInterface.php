<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Factory;

use Setono\PeakWMS\DataTransferObject\Webhook\Webhook;
use Setono\SyliusPeakWMSPlugin\Model\RegisteredWebhooksInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * @extends FactoryInterface<RegisteredWebhooksInterface>
 */
interface RegisteredWebhooksFactoryInterface extends FactoryInterface
{
    public function createNew(): RegisteredWebhooksInterface;

    /**
     * @param list<Webhook> $webhooks
     */
    public function createFromData(string $version, array $webhooks): RegisteredWebhooksInterface;
}
