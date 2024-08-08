<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakPlugin\DataMapper\SalesOrder;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\DataMapper\SalesOrder\SalesOrderLinesSalesOrderDataMapper;
use Sylius\Component\Core\Model\Adjustment;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\Model\OrderItemUnit;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\PaymentMethod;
use Sylius\Component\Core\Model\Product;
use Sylius\Component\Core\Model\ProductVariant;
use Tests\Setono\SyliusPeakPlugin\Application\Model\Order;

final class SalesOrderLinesSalesOrderDataMapperTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function it_maps_order_lines_with_tax_included_in_price(): void
    {
        $order = self::getOrder();
        $order->addItem(self::getOrderItem(100, 20, true));
        $order->addItem(self::getOrderItem(200, 40, true));

        $salesOrder = new SalesOrder();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $mapper = new SalesOrderLinesSalesOrderDataMapper($eventDispatcher->reveal());
        $mapper->map($order, $salesOrder);

        self::assertCount(2, $salesOrder->orderLines);
        self::assertSame(0.8, $salesOrder->orderLines[0]->salesPricePiece);
        self::assertSame(1, $salesOrder->orderLines[0]->quantityRequested);
        self::assertSame(0.2, $salesOrder->orderLines[0]->salesPriceTaxPiece);

        self::assertSame(1.6, $salesOrder->orderLines[1]->salesPricePiece);
        self::assertSame(1, $salesOrder->orderLines[1]->quantityRequested);
        self::assertSame(0.4, $salesOrder->orderLines[1]->salesPriceTaxPiece);
    }

    /**
     * @test
     */
    public function it_maps_order_lines_with_tax_not_included_in_price(): void
    {
        $order = self::getOrder();
        $order->addItem(self::getOrderItem(100, 20, false));
        $order->addItem(self::getOrderItem(200, 40, false));

        $salesOrder = new SalesOrder();

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);

        $mapper = new SalesOrderLinesSalesOrderDataMapper($eventDispatcher->reveal());
        $mapper->map($order, $salesOrder);

        self::assertCount(2, $salesOrder->orderLines);
        self::assertSame(1.0, $salesOrder->orderLines[0]->salesPricePiece);
        self::assertSame(1, $salesOrder->orderLines[0]->quantityRequested);
        self::assertSame(0.2, $salesOrder->orderLines[0]->salesPriceTaxPiece);

        self::assertSame(2.0, $salesOrder->orderLines[1]->salesPricePiece);
        self::assertSame(1, $salesOrder->orderLines[1]->quantityRequested);
        self::assertSame(0.4, $salesOrder->orderLines[1]->salesPriceTaxPiece);
    }

    private static function getOrder(): Order
    {
        $order = new Order();
        $order->setCurrencyCode('USD');

        $paymentMethod = new PaymentMethod();
        $paymentMethod->setCurrentLocale('en_US');
        $paymentMethod->setName('Credit Card');

        $payment = new Payment();
        $payment->setMethod($paymentMethod);

        $order->addPayment($payment);

        return $order;
    }

    private static function getOrderItem(int $unitPrice, int $taxAmount, bool $taxNeutral): OrderItem
    {
        $variant = new ProductVariant();
        $variant->setCode('variant_code');

        $product = new Product();
        $product->setCode('product_code');

        $product->addVariant($variant);

        $orderItem = new OrderItem();
        $orderItem->setUnitPrice($unitPrice);
        $orderItem->setVariant($variant);

        $taxAdjustment = new Adjustment();
        $taxAdjustment->setAmount($taxAmount);
        $taxAdjustment->setNeutral($taxNeutral);
        $taxAdjustment->setType(AdjustmentInterface::TAX_ADJUSTMENT);

        $orderItemUnit = new OrderItemUnit($orderItem);
        $orderItemUnit->addAdjustment($taxAdjustment);

        return $orderItem;
    }
}
