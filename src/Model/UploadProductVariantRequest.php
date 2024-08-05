<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

class UploadProductVariantRequest implements UploadProductVariantRequestInterface
{
    protected ?int $id = null;

    protected int $version = 1;

    protected string $state = self::STATE_PENDING;

    protected ?ProductVariantInterface $productVariant = null;

    protected ?string $request = null;

    protected ?string $response = null;

    protected ?string $error = null;

    protected ?int $peakProductId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(?int $version): void
    {
        $this->version = (int) $version;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): void
    {
        $this->state = $state;
    }

    public function getProductVariant(): ?ProductVariantInterface
    {
        return $this->productVariant;
    }

    public function setProductVariant(?ProductVariantInterface $productVariant): void
    {
        $this->productVariant = $productVariant;
    }

    public function getRequest(): ?string
    {
        return $this->request;
    }

    public function setRequest(?string $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): void
    {
        $this->response = $response;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    public function getPeakProductId(): ?int
    {
        return $this->peakProductId;
    }

    public function setPeakProductId(?int $peakProductId): void
    {
        $this->peakProductId = $peakProductId;
    }
}
