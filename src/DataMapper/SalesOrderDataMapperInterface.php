<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper;

use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\Model\OrderInterface;

// todo move to Setono\PeakWMS\DataMapper\SalesOrder
interface SalesOrderDataMapperInterface
{
    /**
     * Maps an order to a Peak WMS sales order
     */
    public function map(OrderInterface $order, SalesOrder $salesOrder): void;
}
