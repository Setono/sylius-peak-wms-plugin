<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait ProductVariantTrait
{
    /**
     * @ORM\OneToMany(mappedBy="productVariant", targetEntity="Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface", cascade={"persist"}, orphanRemoval=true)
     *
     * @var Collection<array-key, UploadProductVariantRequestInterface>
     */
    #[ORM\OneToMany(mappedBy: 'productVariant', targetEntity: UploadProductVariantRequestInterface::class, cascade: ['persist'], orphanRemoval: true)]
    protected Collection $peakUploadProductVariantRequests;

    public function getPeakUploadProductVariantRequests(): Collection
    {
        return $this->peakUploadProductVariantRequests;
    }

    public function addPeakUploadProductVariantRequest(UploadProductVariantRequestInterface $uploadProductVariantRequest): void
    {
        $this->peakUploadProductVariantRequests->add($uploadProductVariantRequest);
        $uploadProductVariantRequest->setProductVariant($this);
    }
}
