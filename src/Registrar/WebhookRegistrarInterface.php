<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Registrar;

use Setono\SyliusPeakPlugin\Exception\WebhookRegistrationException;

interface WebhookRegistrarInterface
{
    /**
     * Will register your shop with Peak WMS's webhook service.
     *
     * This method is idempotent, meaning that it can be called multiple times without registering the webhooks multiple times
     *
     * @throws WebhookRegistrationException if the registration fails in any way
     */
    public function register(): void;

    /**
     * Returns true if the registered webhooks are out of date
     */
    public function outOfDate(): bool;
}
