<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber\Workflow\InventoryUpdate;

use Setono\SyliusPeakPlugin\Model\InventoryUpdateInterface;
use Setono\SyliusPeakPlugin\Workflow\InventoryUpdateWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final class CompleteSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            sprintf('workflow.%s.completed.%s', InventoryUpdateWorkflow::NAME, InventoryUpdateWorkflow::TRANSITION_COMPLETE) => 'set',
        ];
    }

    public function set(CompletedEvent $event): void
    {
        /** @var InventoryUpdateInterface|object $inventoryUpdate */
        $inventoryUpdate = $event->getSubject();
        Assert::isInstanceOf($inventoryUpdate, InventoryUpdateInterface::class);

        $inventoryUpdate->setCompletedAt(new \DateTimeImmutable());
    }
}
