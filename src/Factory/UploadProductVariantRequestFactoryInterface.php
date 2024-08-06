<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

/**
 * @extends FactoryInterface<UploadProductVariantRequestInterface>
 */
interface UploadProductVariantRequestFactoryInterface extends FactoryInterface
{
    public function createNew(): UploadProductVariantRequestInterface;
}
