<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\CommandHandler;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\Product\Product;
use Setono\PeakWMS\Exception\TooManyRequestsException;
use Setono\SyliusPeakPlugin\DataMapper\Product\ProductDataMapperInterface;
use Setono\SyliusPeakPlugin\Message\Command\ProcessUploadProductVariantRequest;
use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadProductVariantRequestWorkflow;
use Symfony\Component\Messenger\Exception\RecoverableMessageHandlingException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Workflow\WorkflowInterface;

final class ProcessUploadProductVariantRequestHandler extends AbstractProcessUploadRequestHandler
{
    use ORMTrait;

    /**
     * @param class-string<UploadProductVariantRequestInterface> $uploadProductVariantRequestClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly ClientInterface $peakClient,
        private readonly ProductDataMapperInterface $productDataMapper,
        private readonly WorkflowInterface $uploadProductVariantRequestWorkflow,
        private readonly string $uploadProductVariantRequestClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(ProcessUploadProductVariantRequest $message): void
    {
        $manager = $this->getManager($this->uploadProductVariantRequestClass);

        $uploadProductVariantRequest = $manager->find($this->uploadProductVariantRequestClass, $message->uploadProductVariantRequest);
        if (!$uploadProductVariantRequest instanceof UploadProductVariantRequestInterface) {
            throw new UnrecoverableMessageHandlingException(sprintf('Upload product variant request with id %d does not exist', $message->uploadProductVariantRequest));
        }

        if (null !== $message->version && $uploadProductVariantRequest->getVersion() !== $message->version) {
            throw new UnrecoverableMessageHandlingException(sprintf('Upload product variant request with id %d has been updated since it was tried to be processed', $message->uploadProductVariantRequest));
        }

        if ($uploadProductVariantRequest->getState() !== UploadProductVariantRequestInterface::STATE_PROCESSING) {
            throw new UnrecoverableMessageHandlingException(sprintf('Upload product variant request with id %d is not in the processing state', $message->uploadProductVariantRequest));
        }

        $productVariant = $uploadProductVariantRequest->getProductVariant();
        if (null === $productVariant) {
            throw new UnrecoverableMessageHandlingException(sprintf('The upload product variant request with id %d does not have an associated product variant', $message->uploadProductVariantRequest));
        }

        try {
            $product = new Product();
            $this->productDataMapper->map($productVariant, $product);

            if ($uploadProductVariantRequest->getPeakProductId() === null) {
                $response = $this->peakClient->product()->create($product);
                $uploadProductVariantRequest->setPeakProductId($response->id);
            } else {
                // todo test if the product _actually_ exists in Peak
                $this->peakClient->product()->update($product);
            }

            $this->uploadProductVariantRequestWorkflow->apply($uploadProductVariantRequest, UploadProductVariantRequestWorkflow::TRANSITION_UPLOAD);
        } catch (TooManyRequestsException $e) {
            throw new RecoverableMessageHandlingException(
                message: sprintf('There were too many requests to Peak WMS API when trying to process upload product variant request with id %d. The message will be retried later.', $message->uploadProductVariantRequest),
                previous: $e,
            );
        } catch (\Throwable $e) {
            $uploadProductVariantRequest->setError($e->getMessage());

            $this->uploadProductVariantRequestWorkflow->apply($uploadProductVariantRequest, UploadProductVariantRequestWorkflow::TRANSITION_FAIL);

            throw new UnrecoverableMessageHandlingException(
                message: sprintf('Failed to process upload product variant request with id %d', $message->uploadProductVariantRequest),
                previous: $e,
            );
        } finally {
            $uploadProductVariantRequest->setRequest(self::stringifyMessage($this->peakClient->getLastRequest()));
            $uploadProductVariantRequest->setResponse(self::stringifyMessage($this->peakClient->getLastResponse()));

            if ($manager->isOpen()) {
                $manager->flush();
            }

            $message->version = $uploadProductVariantRequest->getVersion();
        }
    }
}
