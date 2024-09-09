<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber;

use Setono\PeakWMS\Client\ClientInterface;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use SM\Event\SMEvents;
use SM\Event\TransitionEvent;
use Sylius\Component\Order\OrderTransitions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class HandleOrderCancellationSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SMEvents::POST_TRANSITION => 'handleOrderCancellation',
        ];
    }

    public function handleOrderCancellation(TransitionEvent $event): void
    {
        $obj = $event->getStateMachine()->getObject();
        if (!$obj instanceof OrderInterface) {
            return;
        }

        if ($event->getTransition() !== OrderTransitions::TRANSITION_CANCEL) {
            return;
        }

        if ($obj->getPeakUploadOrderRequest()?->getPeakOrderId() === null) {
            return;
        }

        $this->client->salesOrder()->cancel((string) $obj->getId());
    }
}
