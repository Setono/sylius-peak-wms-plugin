<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Model;

use Doctrine\ORM\Mapping as ORM;

trait OrderTrait
{
    /**
     * @ORM\OneToOne(inversedBy="order", targetEntity="Setono\SyliusPeakWMSPlugin\Model\UploadOrderRequestInterface", cascade={"persist"}, orphanRemoval=true)
     *
     * @ORM\JoinColumn(name="peak_wms_upload_order_request_id", referencedColumnName="id", unique=true, nullable=true, onDelete="SET NULL")
     */
    #[ORM\OneToOne(inversedBy: 'order', targetEntity: UploadOrderRequestInterface::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\JoinColumn(name: 'peak_wms_upload_order_request_id', referencedColumnName: 'id', unique: true, nullable: true, onDelete: 'SET NULL')]
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
