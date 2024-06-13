<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\DataMapper;

use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakWMSPlugin\Model\OrderInterface;

interface SalesOrderDataMapperInterface
{
    /**
     * Maps an order to a Peak WMS sales order
     */
    public function map(OrderInterface $order, SalesOrder $salesOrder): void;
}
