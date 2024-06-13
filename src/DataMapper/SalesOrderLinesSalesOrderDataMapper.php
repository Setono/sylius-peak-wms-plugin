<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\DataMapper;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\PeakWMS\DataTransferObject\SalesOrder\OrderLine\SalesOrderLine;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakWMSPlugin\Event\SalesOrderLineMappedEvent;
use function Setono\SyliusPeakWMSPlugin\formatAmount;
use Setono\SyliusPeakWMSPlugin\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Webmozart\Assert\Assert;

final class SalesOrderLinesSalesOrderDataMapper implements SalesOrderDataMapperInterface
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function map(OrderInterface $order, SalesOrder $salesOrder): void
    {
        foreach ($order->getItems() as $orderItem) {
            /** @var OrderItemUnitInterface|false $orderItemUnit */
            $orderItemUnit = $orderItem->getUnits()->first();
            Assert::isInstanceOf($orderItemUnit, OrderItemUnitInterface::class);

            $unitPriceExcludingVat = $orderItemUnit->getTotal() - $orderItemUnit->getTaxTotal();

            $orderLine = new SalesOrderLine(
                orderLineId: (string) $orderItem->getId(),
                quantityRequested: $orderItem->getQuantity(),
                productId: $orderItem->getProduct()?->getCode(),
                variantId: $orderItem->getVariant()?->getCode(),
                salesPricePiece: formatAmount($unitPriceExcludingVat),
                salesDiscountTaxPiece: formatAmount($orderItemUnit->getTaxTotal()),
            );

            $this->eventDispatcher->dispatch(new SalesOrderLineMappedEvent($orderLine, $orderItem, $order));

            $salesOrder->orderLines[] = $orderLine;
        }
    }
}
