<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Processor;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Message\Command\ProcessUploadProductVariantRequest;
use Setono\SyliusPeakPlugin\Provider\PreQualifiedUploadProductVariantRequestsProviderInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadProductVariantRequestWorkflow;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\WorkflowInterface;

final class UploadProductVariantRequestProcessor implements UploadProductVariantRequestProcessorInterface
{
    use ORMTrait;

    public function __construct(
        private readonly PreQualifiedUploadProductVariantRequestsProviderInterface $preQualifiedUploadProductVariantRequestsProvider,
        private readonly MessageBusInterface $commandBus,
        private readonly WorkflowInterface $uploadProductVariantRequestWorkflow,
        ManagerRegistry $managerRegistry,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function process(): void
    {
        $delay = $i = 0;

        foreach ($this->preQualifiedUploadProductVariantRequestsProvider->getUploadProductVariantRequests() as $uploadProductVariantRequest) {
            try {
                $this->uploadProductVariantRequestWorkflow->apply($uploadProductVariantRequest, UploadProductVariantRequestWorkflow::TRANSITION_DISPATCH);
            } catch (LogicException) {
                continue;
            }

            try {
                $this->getManager($uploadProductVariantRequest)->flush();
            } catch (OptimisticLockException) {
                continue;
            }

            ++$i;

            // According to https://api.peakwms.com/api/documentation/index.html the rate limit is 240 requests per minute,
            // so we play it safe and dispatch 120 (half) requests per minute
            if ($i % 120 === 0) {
                $delay += 60_000;
            }

            $stamps = [];
            if ($delay > 0) {
                $stamps[] = new DelayStamp($delay);
            }

            $this->commandBus->dispatch(new ProcessUploadProductVariantRequest($uploadProductVariantRequest), $stamps);
        }
    }
}
