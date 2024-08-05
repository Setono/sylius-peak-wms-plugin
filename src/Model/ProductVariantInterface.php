<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Sylius\Component\Core\Model\ProductVariantInterface as BaseProductVariantInterface;

interface ProductVariantInterface extends BaseProductVariantInterface
{
    public function getPeakUploadProductVariantRequest(): ?UploadProductVariantRequestInterface;

    public function setPeakUploadProductVariantRequest(?UploadProductVariantRequestInterface $uploadProductVariantRequest): void;
}
