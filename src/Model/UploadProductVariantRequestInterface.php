<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\VersionedInterface;

interface UploadProductVariantRequestInterface extends ResourceInterface, VersionedInterface
{
    public const STATE_PENDING = 'pending';

    public const STATE_DISPATCHED = 'dispatched';

    public const STATE_PROCESSING = 'processing';

    public const STATE_UPLOADED = 'uploaded';

    public const STATE_FAILED = 'failed';

    public function getId(): ?int;

    public function getState(): string;

    public function setState(string $state): void;

    public function getProductVariant(): ?ProductVariantInterface;

    public function setProductVariant(?ProductVariantInterface $productVariant): void;

    public function getRequest(): ?string;

    public function setRequest(?string $request): void;

    public function getResponse(): ?string;

    public function setResponse(?string $response): void;

    public function getError(): ?string;

    public function setError(?string $error): void;

    /**
     * If the product variant was uploaded successfully, this method will return the peak internal id
     */
    public function getPeakProductId(): ?int;

    public function setPeakProductId(?int $peakProductId): void;
}
