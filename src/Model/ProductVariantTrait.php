<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait ProductVariantTrait
{
    /** @ORM\OneToOne(mappedBy="productVariant", targetEntity="Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface", cascade={"persist"}, orphanRemoval=true) */
    #[ORM\OneToOne(mappedBy: 'productVariant', targetEntity: UploadProductVariantRequestInterface::class, cascade: ['persist'], orphanRemoval: true)]
    protected ?UploadProductVariantRequestInterface $peakUploadProductVariantRequest = null;

    public function getPeakUploadProductVariantRequest(): ?UploadProductVariantRequestInterface
    {
        return $this->peakUploadProductVariantRequest;
    }

    public function setPeakUploadProductVariantRequest(?UploadProductVariantRequestInterface $uploadProductVariantRequest): void
    {
        $this->peakUploadProductVariantRequest = $uploadProductVariantRequest;
        $uploadProductVariantRequest?->setProductVariant($this);
    }
}
