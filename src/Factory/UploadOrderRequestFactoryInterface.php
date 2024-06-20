<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * @extends FactoryInterface<UploadOrderRequestInterface>
 */
interface UploadOrderRequestFactoryInterface extends FactoryInterface
{
    public function createNew(): UploadOrderRequestInterface;
}
