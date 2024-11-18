<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Processor;

interface UploadOrderRequestProcessorInterface
{
    public function process(): void;
}
