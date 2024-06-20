<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Event;

use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\Model\OrderInterface;

/**
 * This event is dispatched after payment details has been mapped
 */
final class SalesOrderMappedEvent
{
    public function __construct(
        public readonly SalesOrder $salesOrder,
        public readonly OrderInterface $order,
    ) {
    }
}
