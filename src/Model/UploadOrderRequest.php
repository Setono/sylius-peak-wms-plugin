<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Model;

class UploadOrderRequest implements UploadOrderRequestInterface
{
    protected ?int $id = null;

    protected int $version = 1;

    protected string $state = self::STATE_PENDING;

    protected ?OrderInterface $order = null;

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
        $this->version = $version;
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
}
