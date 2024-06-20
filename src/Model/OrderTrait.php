<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait OrderTrait
{
    /** @ORM\OneToOne(mappedBy="order", targetEntity="Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface", cascade={"persist"}, orphanRemoval=true) */
    #[ORM\OneToOne(mappedBy: 'order', targetEntity: UploadOrderRequestInterface::class, cascade: ['persist'], orphanRemoval: true)]
    protected ?UploadOrderRequestInterface $peakUploadOrderRequest = null;

    public function getPeakUploadOrderRequest(): ?UploadOrderRequestInterface
    {
        return $this->peakUploadOrderRequest;
    }

    public function setPeakUploadOrderRequest(?UploadOrderRequestInterface $uploadOrderRequest): void
    {
        $this->peakUploadOrderRequest = $uploadOrderRequest;
        $uploadOrderRequest?->setOrder($this);
    }
}
