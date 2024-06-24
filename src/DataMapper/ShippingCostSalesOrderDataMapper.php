<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper;

use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use function Setono\SyliusPeakPlugin\formatAmount;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Webmozart\Assert\Assert;

final class ShippingCostSalesOrderDataMapper implements SalesOrderDataMapperInterface
{
    public function map(OrderInterface $order, SalesOrder $salesOrder): void
    {
        $shipping = $shippingTax = 0;

        foreach ($order->getShipments() as $shipment) {
            $shippingAdjustments = $shipment->getAdjustments(AdjustmentInterface::SHIPPING_ADJUSTMENT);
            if ($shippingAdjustments->isEmpty()) {
                continue;
            }

            // Sylius only adds one shipping adjustment per shipment
            Assert::count($shippingAdjustments, 1);

            $shippingAdjustment = $shippingAdjustments->first();
            Assert::isInstanceOf($shippingAdjustment, AdjustmentInterface::class);

            $taxAdjustments = $shipment->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT);
            Assert::lessThanEq($taxAdjustments->count(), 1);

            // Because of the way shipping and shipping taxes are done in Sylius, we need to do some gymnastics to calculate the correct unit price.
            // If the tax adjustment is neutral we know that the tax is included in the shipping adjustment's amount and hence we subtract the tax.
            // If the tax adjustment is not neutral we know that the tax is not included and hence we don't subtract the tax.
            // The tax is still the same though, and we need that to calculate the vat percentage
            $tax = 0;
            $amount = $shippingAdjustment->getAmount();

            $taxAdjustment = $taxAdjustments->first();
            if (false !== $taxAdjustment) {
                $tax = $taxAdjustment->getAmount();

                if ($taxAdjustment->isNeutral()) {
                    $amount -= $tax;
                }
            }

            $shipping += $amount;
            $shippingTax += $tax;
        }

        $salesOrder->shippingCost = formatAmount($shipping);
        $salesOrder->shippingTaxCost = formatAmount($shippingTax);
    }
}
