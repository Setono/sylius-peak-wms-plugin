<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber\Workflow\UploadOrderRequest;

use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final class RetrySubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly int $maxTries = 5)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [sprintf('workflow.%s.completed.%s', UploadOrderRequestWorkflow::NAME, UploadOrderRequestWorkflow::TRANSITION_FAIL) => 'retry'];
    }

    public function retry(CompletedEvent $event): void
    {
        /** @var UploadOrderRequestInterface|object $uploadOrderRequest */
        $uploadOrderRequest = $event->getSubject();
        Assert::isInstanceOf($uploadOrderRequest, UploadOrderRequestInterface::class);

        if ($uploadOrderRequest->getTries() >= $this->maxTries) {
            return;
        }

        $event->getWorkflow()->apply($uploadOrderRequest, UploadOrderRequestWorkflow::TRANSITION_RESET);
    }
}
