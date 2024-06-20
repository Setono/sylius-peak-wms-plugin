<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\WebhookHandler;

use Setono\SyliusPeakPlugin\Exception\UnsupportedWebhookException;

interface WebhookHandlerInterface
{
    /**
     * @throws UnsupportedWebhookException
     */
    public function handle(object $data): void;

    public function supports(object $data): bool;
}
