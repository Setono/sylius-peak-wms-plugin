<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Model;

use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;

interface OrderInterface extends BaseOrderInterface
{
    public function getPeakWMSUploadOrderRequest(): ?UploadOrderRequestInterface;

    public function setPeakWMSUploadOrderRequest(?UploadOrderRequestInterface $peakWMSUploadOrderRequest): void;
}
