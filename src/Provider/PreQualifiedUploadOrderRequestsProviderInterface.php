<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Provider;

use Setono\SyliusPeakWMSPlugin\Model\UploadOrderRequestInterface;

interface PreQualifiedUploadOrderRequestsProviderInterface
{
    /**
     * @return iterable<array-key, UploadOrderRequestInterface>
     */
    public function getUploadOrderRequests(): iterable;
}
