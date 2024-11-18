<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber\Workflow\UploadOrderRequest;

use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final class StateUpdatedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [sprintf('workflow.%s.completed', UploadOrderRequestWorkflow::NAME) => 'updateTimestamp'];
    }

    public function updateTimestamp(CompletedEvent $event): void
    {
        /** @var UploadOrderRequestInterface|object $uploadOrderRequest */
        $uploadOrderRequest = $event->getSubject();
        Assert::isInstanceOf($uploadOrderRequest, UploadOrderRequestInterface::class);

        $uploadOrderRequest->setStateUpdatedAt(new \DateTimeImmutable());
    }
}
