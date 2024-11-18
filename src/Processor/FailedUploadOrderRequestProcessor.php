<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Processor;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Provider\FailedUploadOrderRequestsProviderInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\WorkflowInterface;

final class FailedUploadOrderRequestProcessor implements FailedUploadOrderRequestProcessorInterface
{
    use ORMTrait;

    public function __construct(
        private readonly FailedUploadOrderRequestsProviderInterface $failedUploadOrderRequestsProvider,
        private readonly WorkflowInterface $uploadOrderRequestWorkflow,
        ManagerRegistry $managerRegistry,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function process(): void
    {
        foreach ($this->failedUploadOrderRequestsProvider->getUploadOrderRequests() as $uploadOrderRequest) {
            try {
                $this->uploadOrderRequestWorkflow->apply($uploadOrderRequest, UploadOrderRequestWorkflow::TRANSITION_FAIL);
            } catch (LogicException) {
                continue;
            }

            try {
                $this->getManager($uploadOrderRequest)->flush();
            } catch (OptimisticLockException) {
                continue;
            }
        }
    }
}
