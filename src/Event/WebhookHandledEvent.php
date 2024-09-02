<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Event;

final class WebhookHandledEvent
{
    public function __construct(
        /**
         * This is one of the Webhook DTO classes from the Setono\PeakWMS\DataTransferObject\Webhook namespace
         */
        public readonly object $webhook,
    ) {
    }
}
