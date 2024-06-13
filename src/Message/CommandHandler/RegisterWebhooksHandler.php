<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Message\CommandHandler;

use Setono\SyliusPeakWMSPlugin\Message\Command\RegisterWebhooks;
use Setono\SyliusPeakWMSPlugin\Registrar\WebhookRegistrarInterface;

final class RegisterWebhooksHandler
{
    public function __construct(private readonly WebhookRegistrarInterface $webhookRegistrar)
    {
    }

    public function __invoke(RegisterWebhooks $message): void
    {
        $this->webhookRegistrar->register();
    }
}
