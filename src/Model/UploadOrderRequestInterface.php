<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\VersionedInterface;

interface UploadOrderRequestInterface extends ResourceInterface, VersionedInterface
{
    public const STATE_PENDING = 'pending';

    public const STATE_PROCESSING = 'processing';

    public const STATE_UPLOADED = 'uploaded';

    public const STATE_FAILED = 'failed';

    public function getId(): ?int;

    public function getState(): string;

    public function setState(string $state): void;

    public function getStateUpdatedAt(): ?\DateTimeInterface;

    public function setStateUpdatedAt(\DateTimeInterface $stateUpdatedAt): void;

    public function getOrder(): ?OrderInterface;

    public function setOrder(?OrderInterface $order): void;

    public function getRequest(): ?string;

    public function setRequest(?string $request): void;

    public function getResponse(): ?string;

    public function setResponse(?string $response): void;

    public function getError(): ?string;

    public function setError(?string $error): void;

    /**
     * If the order was uploaded successfully, this method will return the peak order id
     */
    public function getPeakOrderId(): ?int;

    public function setPeakOrderId(?int $peakOrderId): void;

    public function getTries(): int;

    public function setTries(int $tries): void;

    public function incrementTries(): void;
}
