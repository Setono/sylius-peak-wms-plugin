<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Doctrine\Common\Collections\Collection;
use Sylius\Component\Core\Model\ProductVariantInterface as BaseProductVariantInterface;

interface ProductVariantInterface extends BaseProductVariantInterface
{
    /**
     * @return Collection<array-key, UploadProductVariantRequestInterface>
     */
    public function getPeakUploadProductVariantRequests(): Collection;

    public function addPeakUploadProductVariantRequest(UploadProductVariantRequestInterface $uploadProductVariantRequest): void;
}
