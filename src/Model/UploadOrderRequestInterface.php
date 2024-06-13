<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Model;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\VersionedInterface;

interface UploadOrderRequestInterface extends ResourceInterface, VersionedInterface
{
    public const STATE_PENDING = 'pending';

    public const STATE_PROCESSING = 'processing';

    public const STATE_UPLOADED = 'uploaded';

    public const STATE_FAILED = 'failed';

    public function getId(): ?int;

    public function getState(): ?string;

    public function setState(string $state): void;

    public function getOrder(): ?OrderInterface;
}
