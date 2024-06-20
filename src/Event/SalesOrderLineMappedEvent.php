<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Event;

use Setono\PeakWMS\DataTransferObject\SalesOrder\OrderLine\SalesOrderLine;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;

/**
 * This event is dispatched when we map order items to sales order lines
 */
final class SalesOrderLineMappedEvent
{
    public function __construct(
        /**
         * This is the sales order line that will be uploaded to Peak WMS
         */
        public readonly SalesOrderLine $salesOrderLine,
        /**
         * This is the order item that the sales order line is based on
         */
        public readonly OrderItemInterface $orderItem,
        /**
         * This is the order that the order item belongs to
         */
        public readonly OrderInterface $order,
    ) {
    }
}
