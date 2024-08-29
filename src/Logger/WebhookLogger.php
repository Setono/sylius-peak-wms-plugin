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

    /**
     * @param mixed $level
     * @param string $message
     */
    public function log($level, $message, array $context = []): void
    {
        /** @psalm-suppress RedundantCastGivenDocblockType */
        $this->webhook->addLog(sprintf('[%s] %s', (new \DateTimeImmutable())->format(\DATE_ATOM), (string) $message));
    }
}
