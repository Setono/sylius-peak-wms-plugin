<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Event;

use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\Model\OrderInterface;

final class SalesOrderMappedEvent
{
    public function __construct(
        public readonly SalesOrder $salesOrder,
        public readonly OrderInterface $order,
    ) {
    }
}
