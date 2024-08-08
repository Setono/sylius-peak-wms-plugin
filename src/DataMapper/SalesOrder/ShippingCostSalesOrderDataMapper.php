<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper\SalesOrder;

use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use function Setono\SyliusPeakPlugin\formatAmount;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;

final class ShippingCostSalesOrderDataMapper implements SalesOrderDataMapperInterface
{
    public function map(OrderInterface $order, SalesOrder $salesOrder): void
    {
        $tax = 0;

        foreach ($order->getShipments() as $shipment) {
            foreach ($shipment->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT) as $taxAdjustment) {
                $tax += $taxAdjustment->getAmount();
            }
        }

        $salesOrder->shippingCost = formatAmount($order->getShippingTotal() - $tax);
        $salesOrder->shippingTaxCost = formatAmount($tax);
    }
}
