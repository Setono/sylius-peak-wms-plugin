<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

class RegisteredWebhooks implements RegisteredWebhooksInterface
{
    protected ?int $id = null;

    protected ?string $version = null;

    protected array $webhooks = [];

    protected \DateTimeInterface $registeredAt;

    public function __construct()
    {
        $this->registeredAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): void
    {
        $this->version = $version;
    }

    public function getWebhooks(): array
    {
        return $this->webhooks;
    }

    public function setWebhooks(array $webhooks): void
    {
        $this->webhooks = $webhooks;
    }

    public function getRegisteredAt(): \DateTimeInterface
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeInterface $registeredAt): void
    {
        $this->registeredAt = $registeredAt;
    }
}
