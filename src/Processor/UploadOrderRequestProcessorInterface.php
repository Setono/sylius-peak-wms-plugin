<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Processor;

interface UploadOrderRequestProcessorInterface
{
    /**
     * Processes upload order requests
     */
    public function process(): void;
}
