<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;

interface PreQualifiedUploadProductVariantRequestsProviderInterface
{
    /**
     * @return iterable<array-key, UploadProductVariantRequestInterface>
     */
    public function getUploadProductVariantRequests(): iterable;
}
