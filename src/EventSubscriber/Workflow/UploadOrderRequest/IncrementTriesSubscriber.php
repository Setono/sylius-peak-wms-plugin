<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber\Workflow\UploadOrderRequest;

use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final class IncrementTriesSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [sprintf('workflow.%s.completed.%s', UploadOrderRequestWorkflow::NAME, UploadOrderRequestWorkflow::TRANSITION_PROCESS) => 'incrementTries'];
    }

    public function incrementTries(CompletedEvent $event): void
    {
        /** @var UploadOrderRequestInterface|object $uploadOrderRequest */
        $uploadOrderRequest = $event->getSubject();
        Assert::isInstanceOf($uploadOrderRequest, UploadOrderRequestInterface::class);

        $uploadOrderRequest->incrementTries();
    }
}
