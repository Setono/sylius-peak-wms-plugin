<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventListener\Doctrine;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Setono\SyliusPeakPlugin\Factory\UploadOrderRequestFactoryInterface;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\Workflow\WorkflowInterface;

final class OrderListener
{
    public function __construct(
        private readonly UploadOrderRequestFactoryInterface $uploadOrderRequestFactory,
        private readonly WorkflowInterface $uploadOrderRequestWorkflow,
    ) {
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        $obj = $eventArgs->getObject();
        if (!$obj instanceof OrderInterface) {
            return;
        }

        // For now this listener only triggers when the state changes
        if (!$eventArgs->hasChangedField('state')) {
            return;
        }

        $uploadOrderRequest = $obj->getPeakUploadOrderRequest() ?? $this->uploadOrderRequestFactory->createNew();
        $this->uploadOrderRequestWorkflow->apply($uploadOrderRequest, UploadOrderRequestWorkflow::TRANSITION_RESET);

        $obj->setPeakUploadOrderRequest($uploadOrderRequest);
    }
}
