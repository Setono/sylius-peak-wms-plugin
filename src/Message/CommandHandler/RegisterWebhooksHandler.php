<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\CommandHandler;

use Setono\SyliusPeakPlugin\Message\Command\RegisterWebhooks;
use Setono\SyliusPeakPlugin\Registrar\WebhookRegistrarInterface;

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
