<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Logger;

use Psr\Log\AbstractLogger;
use Setono\SyliusPeakPlugin\Model\WebhookInterface;

final class WebhookLogger extends AbstractLogger
{
    public function __construct(private readonly WebhookInterface $webhook)
    {
    }

    public function log($level, \Stringable|string $message, array $context = []): void
    {
        $this->webhook->addLog(sprintf('[%s] %s', (new \DateTimeImmutable())->format(\DATE_ATOM), (string) $message));
    }
}
