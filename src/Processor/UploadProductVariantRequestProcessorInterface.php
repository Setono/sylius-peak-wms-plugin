<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Processor;

interface UploadProductVariantRequestProcessorInterface
{
    /**
     * Processes upload product variant requests
     */
    public function process(): void;
}
