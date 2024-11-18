<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Processor;

interface FailedUploadOrderRequestProcessorInterface
{
    public function process(): void;
}
