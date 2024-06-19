<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Message\CommandHandler;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
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

        try {
            $salesOrder = new SalesOrder();
            $this->salesOrderDataMapper->map($order, $salesOrder);

            $this->peakWMSClient->salesOrder()->create($salesOrder);

            $this->uploadOrderRequestWorkflow->apply($order, UploadOrderRequestWorkflow::TRANSITION_UPLOAD);
        } catch (\Throwable $e) {
            $uploadOrderRequest->setError($e->getMessage());

            $this->uploadOrderRequestWorkflow->apply($order, UploadOrderRequestWorkflow::TRANSITION_FAIL);

            throw new UnrecoverableMessageHandlingException(
                message: sprintf('Failed to process upload order request with id %d', $message->uploadOrderRequest),
                previous: $e,
            );
        } finally {
            $uploadOrderRequest->setRequest(self::stringifyMessage($this->peakWMSClient->getLastRequest()));
            $uploadOrderRequest->setResponse(self::stringifyMessage($this->peakWMSClient->getLastResponse()));
            $manager->flush();
        }
    }

    private static function stringifyMessage(RequestInterface|ResponseInterface|null $message): ?string
    {
        if (null === $message) {
            return null;
        }

        $result = '';
        if ($message instanceof RequestInterface) {
            $result = sprintf(
                "%s %s HTTP/%s\n",
                $message->getMethod(),
                $message->getUri(),
                $message->getProtocolVersion(),
            );
        }

        foreach ($message->getHeaders() as $name => $values) {
            $result .= sprintf("%s: %s\n", $name, implode(', ', $values));
        }

        $body = trim((string) $message->getBody());
        if ('' !== $body) {
            $result .= "\n\n" . $body;
        }

        return $result;
    }
}
