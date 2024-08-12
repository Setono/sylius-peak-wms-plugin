<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\PeakWMS\DataTransferObject\Webhook\Webhook;
use Setono\SyliusPeakPlugin\Model\WebhookRegistrationInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * @extends FactoryInterface<WebhookRegistrationInterface>
 */
interface WebhookRegistrationFactoryInterface extends FactoryInterface
{
    public function createNew(): WebhookRegistrationInterface;

    /**
     * @param list<Webhook> $webhooks
     */
    public function createFromData(string $version, array $webhooks): WebhookRegistrationInterface;
}
