<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Processor;

interface UploadOrderRequestProcessorInterface
{
    /**
     * Processes upload order requests
     */
    public function process(): void;
}
