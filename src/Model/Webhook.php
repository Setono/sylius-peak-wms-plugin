<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Model;

class Webhook implements WebhookInterface
{
    protected ?int $id = null;

    protected ?string $method = null;

    protected ?string $url = null;

    /** @var array<string, list<string|null>>|null */
    protected ?array $headers = null;

    protected ?string $body = null;

    protected ?string $remoteIp = null;

    protected ?string $log = null;

    protected \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): void
    {
        $this->method = $method;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getHeaders(): array
    {
        return $this->headers ?? [];
    }

    public function setHeaders(?array $headers): void
    {
        $this->headers = $headers;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): void
    {
        $this->body = $body;
    }

    public function getRemoteIp(): ?string
    {
        return $this->remoteIp;
    }

    public function setRemoteIp(?string $remoteIp): void
    {
        $this->remoteIp = $remoteIp;
    }

    public function getLog(): ?string
    {
        return $this->log;
    }

    public function setLog(?string $log): void
    {
        $this->log = $log;
    }

    public function addLog(string $log): void
    {
        if (null === $this->log) {
            $this->log = '';
        }

        $this->log = $log . \PHP_EOL . $this->log;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
