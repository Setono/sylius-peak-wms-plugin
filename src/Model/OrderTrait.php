<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait OrderTrait
{
    /** @ORM\OneToOne(mappedBy="order", targetEntity="Setono\SyliusPeakWMSPlugin\Model\UploadOrderRequestInterface", cascade={"persist"}, orphanRemoval=true) */
    #[ORM\OneToOne(mappedBy: 'order', targetEntity: UploadOrderRequestInterface::class, cascade: ['persist'], orphanRemoval: true)]
    protected ?UploadOrderRequestInterface $peakWMSUploadOrderRequest = null;

    public function getPeakWMSUploadOrderRequest(): ?UploadOrderRequestInterface
    {
        return $this->peakWMSUploadOrderRequest;
    }

    public function setPeakWMSUploadOrderRequest(?UploadOrderRequestInterface $peakWMSUploadOrderRequest): void
    {
        $this->peakWMSUploadOrderRequest = $peakWMSUploadOrderRequest;
        $peakWMSUploadOrderRequest?->setOrder($this);
    }
}
