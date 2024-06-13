<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Registrar;

use Setono\PeakWMS\DataTransferObject\Webhook\Webhook;

interface WebhookRegistrarInterface
{
    /**
     * Will register your shop with Peak WMS's webhook service
     */
    public function register(): void;

    /**
     * Will return a list of webhooks that this registrar will register
     *
     * @return iterable<Webhook>
     */
    public function getWebhooks(): iterable;

    /**
     * Should return a version (string) that uniquely identifies the webhooks being registered
     */
    public function getVersion(): string;
}
