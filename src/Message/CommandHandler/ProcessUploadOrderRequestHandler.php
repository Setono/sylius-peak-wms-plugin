<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\CommandHandler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\DataMapper\SalesOrder\SalesOrderDataMapperInterface;
use Setono\SyliusPeakPlugin\Message\Command\ProcessUploadOrderRequest;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Workflow\WorkflowInterface;

final class ProcessUploadOrderRequestHandler extends AbstractProcessUploadRequestHandler
{
    use ORMTrait;

    /**
     * @param class-string<UploadOrderRequestInterface> $uploadOrderRequestClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly ClientInterface $peakClient,
        private readonly SalesOrderDataMapperInterface $salesOrderDataMapper,
        private readonly WorkflowInterface $uploadOrderRequestWorkflow,
        private readonly string $uploadOrderRequestClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(ProcessUploadOrderRequest $message): void
    {
        $manager = $this->getManager($this->uploadOrderRequestClass);

        $uploadOrderRequest = $manager->find($this->uploadOrderRequestClass, $message->uploadOrderRequest);
        if (!$uploadOrderRequest instanceof UploadOrderRequestInterface) {
            throw new UnrecoverableMessageHandlingException(sprintf('Upload order request with id %d does not exist', $message->uploadOrderRequest));
        }

        if (null !== $message->version && $uploadOrderRequest->getVersion() !== $message->version) {
            throw new UnrecoverableMessageHandlingException(sprintf('Upload order request with id %d has been updated since it was tried to be processed', $message->uploadOrderRequest));
        }

        $order = $uploadOrderRequest->getOrder();
        if (null === $order) {
            throw new UnrecoverableMessageHandlingException(sprintf('The upload order request with id %d does not have an associated order', $message->uploadOrderRequest));
        }

        match ($order->getState()) {
            OrderInterface::STATE_CANCELLED => $this->handleCancelledState($order, $uploadOrderRequest),
            default => $this->handleOtherStates($message, $manager, $order, $uploadOrderRequest),
        };
    }

    public function handleCancelledState(OrderInterface $order, UploadOrderRequestInterface $uploadOrderRequest): void
    {
        if ($uploadOrderRequest->getPeakOrderId() === null) {
            return;
        }

        $this->peakClient->salesOrder()->cancel((string) $order->getId());
    }

    private function handleOtherStates(
        ProcessUploadOrderRequest $message,
        EntityManagerInterface $manager,
        OrderInterface $order,
        UploadOrderRequestInterface $uploadOrderRequest,
    ): void {
        try {
            $salesOrder = new SalesOrder();
            $this->salesOrderDataMapper->map($order, $salesOrder);

            if ($uploadOrderRequest->getPeakOrderId() === null) {
                $response = $this->peakClient->salesOrder()->create($salesOrder);
                $uploadOrderRequest->setPeakOrderId($response->id);
            } else {
                // todo test if the sales order _actually_ exists in Peak
                $this->peakClient->salesOrder()->update($salesOrder, $salesOrder->orderId);
            }

            $this->uploadOrderRequestWorkflow->apply($uploadOrderRequest, UploadOrderRequestWorkflow::TRANSITION_UPLOAD);
        } catch (\Throwable $e) {
            $uploadOrderRequest->setError($e->getMessage());

            $this->uploadOrderRequestWorkflow->apply($uploadOrderRequest, UploadOrderRequestWorkflow::TRANSITION_FAIL);

            throw new UnrecoverableMessageHandlingException(
                message: sprintf('Failed to process upload order request with id %d', $message->uploadOrderRequest),
                previous: $e,
            );
        } finally {
            $uploadOrderRequest->setRequest(self::stringifyMessage($this->peakClient->getLastRequest()));
            $uploadOrderRequest->setResponse(self::stringifyMessage($this->peakClient->getLastResponse()));

            if ($manager->isOpen()) {
                $manager->flush();
            }
        }
    }
}
