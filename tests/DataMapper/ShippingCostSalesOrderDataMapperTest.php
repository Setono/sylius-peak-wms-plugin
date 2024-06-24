<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakPlugin\DataMapper;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\DataMapper\ShippingCostSalesOrderDataMapper;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Shipment;
use Tests\Setono\SyliusPeakPlugin\Application\Model\Order;

final class ShippingCostSalesOrderDataMapperTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_maps_shipping_with_tax_included_in_price(): void
    {
        $order = self::getOrder();
        self::addShipment($order, 100, 20, true);

        $salesOrder = new SalesOrder();

        $mapper = new ShippingCostSalesOrderDataMapper();
        $mapper->map($order, $salesOrder);

        self::assertSame(0.8, $salesOrder->shippingCost);
        self::assertSame(0.2, $salesOrder->shippingTaxCost);
    }

    /**
     * @test
     */
    public function it_maps_shipping_with_tax_not_included_in_price(): void
    {
        $order = self::getOrder();
        self::addShipment($order, 100, 20, false);

        $salesOrder = new SalesOrder();

        $mapper = new ShippingCostSalesOrderDataMapper();
        $mapper->map($order, $salesOrder);

        self::assertSame(1.0, $salesOrder->shippingCost);
        self::assertSame(0.2, $salesOrder->shippingTaxCost);
    }

    private static function getOrder(): Order
    {
        $order = new Order();
        $order->setCurrencyCode('USD');

        return $order;
    }

    private static function addShipment(OrderInterface $order, int $shippingAmount, int $taxAmount, bool $taxNeutral): void
    {
        $shipment = new Shipment();
        $order->addShipment($shipment);

        $taxAdjustment = new Adjustment();
        $taxAdjustment->setAmount($taxAmount);
        $taxAdjustment->setNeutral($taxNeutral);
        $taxAdjustment->setType(AdjustmentInterface::TAX_ADJUSTMENT);

        $shippingAdjustment = new Adjustment();
        $shippingAdjustment->setAmount($shippingAmount);
        $shippingAdjustment->setNeutral(false);
        $shippingAdjustment->setType(AdjustmentInterface::SHIPPING_ADJUSTMENT);

        $shipment->addAdjustment($shippingAdjustment);
        $shipment->addAdjustment($taxAdjustment);
    }
}
