<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Message\CommandHandler;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakWMSPlugin\DataMapper\SalesOrderDataMapperInterface;
use Setono\SyliusPeakWMSPlugin\Message\Command\ProcessUploadOrderRequest;
use Setono\SyliusPeakWMSPlugin\Model\UploadOrderRequestInterface;
use Setono\SyliusPeakWMSPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Workflow\WorkflowInterface;

final class ProcessUploadOrderRequestHandler
{
    use ORMTrait;

    /**
     * @param class-string<UploadOrderRequestInterface> $uploadOrderRequestClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly ClientInterface $peakWMSClient,
        private readonly SalesOrderDataMapperInterface $salesOrderDataMapper,
        private readonly WorkflowInterface $uploadOrderRequestWorkflow,
        private readonly string $uploadOrderRequestClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(ProcessUploadOrderRequest $message): void
    {
        $uploadOrderRequest = $this->getManager($this->uploadOrderRequestClass)->find($this->uploadOrderRequestClass, $message->uploadOrderRequest);
        if (!$uploadOrderRequest instanceof UploadOrderRequestInterface) {
            throw new UnrecoverableMessageHandlingException(sprintf('Upload order request with id %d does not exist', $message->uploadOrderRequest));
        }

        if (null !== $message->version && $uploadOrderRequest->getVersion() !== $message->version) {
            throw new UnrecoverableMessageHandlingException(sprintf('Upload order request with id %d has been updated since it was tried to be processed', $message->uploadOrderRequest));
        }

        // todo try catch exceptions and log errors

        $order = $uploadOrderRequest->getOrder();
        if (null === $order) {
            throw new UnrecoverableMessageHandlingException(sprintf('The upload order request with id %d does not have an associated order', $message->uploadOrderRequest));
        }

        $salesOrder = new SalesOrder();
        $this->salesOrderDataMapper->map($order, $salesOrder);

        $this->peakWMSClient->salesOrder()->create($salesOrder);

        $this->uploadOrderRequestWorkflow->apply($order, UploadOrderRequestWorkflow::TRANSITION_UPLOAD);
    }
}
