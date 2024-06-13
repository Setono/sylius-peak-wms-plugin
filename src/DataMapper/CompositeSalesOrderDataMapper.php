<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\DataMapper;

use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\CompositeCompilerPass\CompositeService;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakWMSPlugin\Event\SalesOrderMappedEvent;
use Setono\SyliusPeakWMSPlugin\Model\OrderInterface;

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
