<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\Command;

use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;

final class ProcessUploadProductVariantRequest implements CommandInterface
{
    /**
     * The upload product variant request id
     */
    public int $uploadProductVariantRequest;

    /**
     * If the version is set, it will be used to check if the upload product variant request has been updated since it was triggered for processing
     */
    public ?int $version = null;

    public function __construct(int|UploadProductVariantRequestInterface $uploadProductVariantRequest, int $version = null)
    {
        if ($uploadProductVariantRequest instanceof UploadProductVariantRequestInterface) {
            $version = $uploadProductVariantRequest->getVersion();

            $uploadProductVariantRequest = (int) $uploadProductVariantRequest->getId();
        }

        $this->uploadProductVariantRequest = $uploadProductVariantRequest;
        $this->version = $version;
    }
}
