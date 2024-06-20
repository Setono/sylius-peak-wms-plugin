<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;

interface OrderInterface extends BaseOrderInterface
{
    public function getPeakUploadOrderRequest(): ?UploadOrderRequestInterface;

    public function setPeakUploadOrderRequest(?UploadOrderRequestInterface $uploadOrderRequest): void;
}
