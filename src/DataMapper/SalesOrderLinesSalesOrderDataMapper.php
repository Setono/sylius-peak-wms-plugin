<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\PeakWMS\DataTransferObject\SalesOrder\OrderLine\SalesOrderLine;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\Event\SalesOrderLineMappedEvent;
use function Setono\SyliusPeakPlugin\formatAmount;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemUnitInterface;
use Webmozart\Assert\Assert;

// todo test with adjustments
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
                salesPriceTaxPiece: formatAmount($orderItemUnit->getTaxTotal()),
            );

            $this->eventDispatcher->dispatch(new SalesOrderLineMappedEvent($orderLine, $orderItem, $order));

            $salesOrder->orderLines[] = $orderLine;
        }
    }
}
