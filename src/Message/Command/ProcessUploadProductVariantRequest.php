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

    public function __construct(int|UploadProductVariantRequestInterface $uploadProductVariantRequest)
    {
        if ($uploadProductVariantRequest instanceof UploadProductVariantRequestInterface) {
            $uploadProductVariantRequest = (int) $uploadProductVariantRequest->getId();
        }

        $this->uploadProductVariantRequest = $uploadProductVariantRequest;
    }
}
