<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Processor;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakWMSPlugin\Message\Command\ProcessUploadOrderRequest;
use Setono\SyliusPeakWMSPlugin\Provider\PreQualifiedUploadOrderRequestsProviderInterface;
use Setono\SyliusPeakWMSPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Component\Workflow\WorkflowInterface;

final class UploadOrderRequestProcessor implements UploadOrderRequestProcessorInterface
{
    use ORMTrait;

    public function __construct(
        private readonly PreQualifiedUploadOrderRequestsProviderInterface $preQualifiedUploadableOrdersProvider,
        private readonly MessageBusInterface $commandBus,
        private readonly WorkflowInterface $orderWorkflow,
        ManagerRegistry $managerRegistry,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function process(): void
    {
        // todo: check for eligibility

        foreach ($this->preQualifiedUploadableOrdersProvider->getUploadOrderRequests() as $order) {
            try {
                $this->orderWorkflow->apply($order, UploadOrderRequestWorkflow::TRANSITION_PROCESS);
            } catch (LogicException) {
                continue;
            }

            try {
                $this->getManager($order)->flush();
            } catch (OptimisticLockException) {
                continue;
            }

            $this->commandBus->dispatch(new ProcessUploadOrderRequest($order));
        }
    }
}
