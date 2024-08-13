<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Factory;

use Setono\SyliusPeakPlugin\Model\WebhookInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends FactoryInterface<WebhookInterface>
 */
interface WebhookFactoryInterface extends FactoryInterface
{
    public function createNew(): WebhookInterface;

    public function createFromRequest(Request $request): WebhookInterface;
}
