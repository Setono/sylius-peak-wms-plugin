<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Model;

class UploadOrderRequest implements UploadOrderRequestInterface
{
    protected ?int $id = null;

    protected int $version = 1;

    protected string $state = self::STATE_PENDING;

    protected ?OrderInterface $order = null;

    protected ?string $request = null;

    protected ?string $response = null;

    protected ?string $error = null;

    protected ?int $peakOrderId = null;

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

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function setOrder(?OrderInterface $order): void
    {
        $this->order = $order;
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

    public function getPeakOrderId(): ?int
    {
        return $this->peakOrderId;
    }

    public function setPeakOrderId(?int $peakOrderId): void
    {
        $this->peakOrderId = $peakOrderId;
    }
}
