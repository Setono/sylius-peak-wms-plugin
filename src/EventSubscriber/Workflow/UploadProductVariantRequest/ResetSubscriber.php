<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber\Workflow\UploadProductVariantRequest;

use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadProductVariantRequestWorkflow;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final class ResetSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            sprintf(
                'workflow.%s.completed.%s',
                UploadProductVariantRequestWorkflow::NAME,
                UploadProductVariantRequestWorkflow::TRANSITION_RESET,
            ) => 'reset',
        ];
    }

    public function reset(CompletedEvent $event): void
    {
        /** @var UploadProductVariantRequestInterface|object $uploadProductVariantRequest */
        $uploadProductVariantRequest = $event->getSubject();
        Assert::isInstanceOf($uploadProductVariantRequest, UploadProductVariantRequestInterface::class);

        $uploadProductVariantRequest->setRequest(null);
        $uploadProductVariantRequest->setResponse(null);
        $uploadProductVariantRequest->setError(null);
    }
}
