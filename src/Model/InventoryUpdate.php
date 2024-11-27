<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

class InventoryUpdate implements InventoryUpdateInterface
{
    protected ?int $id = null;

    protected int $version = 1;

    protected string $state = InventoryUpdateInterface::STATE_PENDING;

    protected ?\DateTimeInterface $processingStartedAt = null;

    protected ?\DateTimeInterface $completedAt = null;

    protected int $productsProcessed = 0;

    /** @var list<string>|null */
    protected ?array $warnings = [];

    /** @var list<string>|null */
    protected ?array $errors = [];

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

    public function getProcessingStartedAt(): ?\DateTimeInterface
    {
        return $this->processingStartedAt;
    }

    public function setProcessingStartedAt(?\DateTimeInterface $processingStartedAt): void
    {
        $this->processingStartedAt = $processingStartedAt;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeInterface $completedAt): void
    {
        $this->completedAt = $completedAt;
    }

    public function getProductsProcessed(): int
    {
        return $this->productsProcessed;
    }

    public function setProductsProcessed(int $productsProcessed): void
    {
        $this->productsProcessed = $productsProcessed;
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function getWarnings(): array
    {
        return $this->warnings ?? [];
    }

    public function setWarnings(?array $warnings): void
    {
        if ([] === $warnings) {
            $warnings = null;
        }

        $this->warnings = $warnings;
    }

    public function hasWarnings(): bool
    {
        return [] !== $this->warnings;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function getErrors(): array
    {
        return $this->errors ?? [];
    }

    public function setErrors(?array $errors): void
    {
        if ([] === $errors) {
            $errors = null;
        }

        $this->errors = $errors;
    }

    public function hasErrors(): bool
    {
        return [] !== $this->errors;
    }
}
