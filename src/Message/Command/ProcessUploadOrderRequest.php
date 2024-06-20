<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\Command;

use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;

/**
 * Processes an upload order request
 */
final class ProcessUploadOrderRequest implements CommandInterface
{
    /**
     * The upload order request id
     */
    public int $uploadOrderRequest;

    /**
     * If the version is set, it will be used to check if the upload order request has been updated since it was triggered for processing
     */
    public ?int $version = null;

    public function __construct(int|UploadOrderRequestInterface $uploadOrderRequest, int $version = null)
    {
        if ($uploadOrderRequest instanceof UploadOrderRequestInterface) {
            $version = $uploadOrderRequest->getVersion();

            $uploadOrderRequest = (int) $uploadOrderRequest->getId();
        }

        $this->uploadOrderRequest = $uploadOrderRequest;
        $this->version = $version;
    }
}
