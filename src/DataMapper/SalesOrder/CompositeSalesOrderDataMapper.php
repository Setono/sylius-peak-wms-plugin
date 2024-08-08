<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper\SalesOrder;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\CompositeCompilerPass\CompositeService;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\Event\SalesOrderMappedEvent;
use Setono\SyliusPeakPlugin\Model\OrderInterface;

/**
 * @extends CompositeService<SalesOrderDataMapperInterface>
 */
final class CompositeSalesOrderDataMapper extends CompositeService implements SalesOrderDataMapperInterface
{
    public function __construct(private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function map(OrderInterface $order, SalesOrder $salesOrder): void
    {
        foreach ($this->services as $service) {
            $service->map($order, $salesOrder);
        }

        $this->eventDispatcher->dispatch(new SalesOrderMappedEvent($salesOrder, $order));
    }
}
