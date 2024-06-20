<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;

interface PreQualifiedUploadOrderRequestsProviderInterface
{
    /**
     * @return iterable<array-key, UploadOrderRequestInterface>
     */
    public function getUploadOrderRequests(): iterable;
}
