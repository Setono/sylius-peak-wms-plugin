<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

use Sylius\Component\Resource\Model\ResourceInterface;

interface WebhookInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getMethod(): ?string;

    public function setMethod(?string $method): void;

    public function getUrl(): ?string;

    public function setUrl(?string $url): void;

    public function getHeaders(): array;

    public function setHeaders(?array $headers): void;

    public function getBody(): ?string;

    public function setBody(?string $body): void;

    public function getRemoteIp(): ?string;

    public function setRemoteIp(?string $remoteIp): void;

    public function getLog(): ?string;

    public function setLog(?string $log): void;

    public function addLog(string $log): void;

    public function getCreatedAt(): \DateTimeInterface;
}
