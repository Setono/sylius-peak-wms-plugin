<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\VersionedInterface;

interface InventoryUpdateInterface extends ResourceInterface, VersionedInterface
{
    final public const STATE_PENDING = 'pending';

    final public const STATE_PROCESSING = 'processing';

    final public const STATE_COMPLETED = 'completed';

    final public const STATE_FAILED = 'failed';

    public function getId(): ?int;

    public function getState(): string;

    public function setState(string $state): void;

    /**
     * This is the time when the inventory update started processing. If null, it means that the inventory update has never been processed.
     */
    public function getProcessingStartedAt(): ?\DateTimeInterface;

    public function setProcessingStartedAt(?\DateTimeInterface $processingStartedAt): void;

    /**
     * This is the time when the inventory update was completed. If null, it means that the inventory update has not been completed yet.
     */
    public function getCompletedAt(): ?\DateTimeInterface;

    public function setCompletedAt(?\DateTimeInterface $completedAt): void;

    /**
     * This is the threshold to use when fetching updated products from Peak WMS. If null, it means that all products should be fetched.
     */
    public function getNextUpdateThreshold(): ?\DateTimeInterface;

    public function setNextUpdateThreshold(?\DateTimeInterface $nextUpdateThreshold): void;

    public function getProductsProcessed(): int;

    public function setProductsProcessed(int $productsProcessed): void;

    public function addWarning(string $warning): void;

    /**
     * @return list<string>
     */
    public function getWarnings(): array;

    /**
     * @param list<string>|null $warnings
     */
    public function setWarnings(?array $warnings): void;

    public function hasWarnings(): bool;

    /**
     * @return list<string>
     */
    public function getErrors(): array;

    public function addError(string $error): void;

    /**
     * @param list<string>|null $errors
     */
    public function setErrors(?array $errors): void;

    public function hasErrors(): bool;
}
