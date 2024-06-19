<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\WebhookHandler;

use Setono\SyliusPeakWMSPlugin\Exception\UnsupportedWebhookException;

interface WebhookHandlerInterface
{
    /**
     * @throws UnsupportedWebhookException
     */
    public function handle(object $data): void;

    public function supports(object $data): bool;
}
