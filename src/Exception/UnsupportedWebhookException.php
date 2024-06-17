<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Exception;

final class UnsupportedWebhookException extends \RuntimeException
{
    public static function fromData(object $data): self
    {
        return new self('Unsupported webhook: ' . $data::class);
    }
}
