<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Exception;

final class UnsupportedWebhookException extends \RuntimeException
{
    public static function fromData(object $data): self
    {
        return new self('Unsupported webhook: ' . $data::class);
    }
}
