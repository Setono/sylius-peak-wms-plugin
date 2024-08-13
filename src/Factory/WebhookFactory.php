<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\SyliusPeakPlugin\Model\WebhookInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Webmozart\Assert\Assert;

final class WebhookFactory implements WebhookFactoryInterface
{
    public function __construct(private readonly FactoryInterface $decorated)
    {
    }

    public function createNew(): WebhookInterface
    {
        $obj = $this->decorated->createNew();
        Assert::isInstanceOf($obj, WebhookInterface::class);

        return $obj;
    }

    public function createFromRequest(Request $request): WebhookInterface
    {
        $obj = $this->createNew();
        $obj->setMethod($request->getMethod());
        $obj->setUrl($request->getUri());
        $obj->setHeaders($request->headers->all());
        $obj->setBody($request->getContent());
        $obj->setRemoteIp($request->getClientIp());

        return $obj;
    }
}
