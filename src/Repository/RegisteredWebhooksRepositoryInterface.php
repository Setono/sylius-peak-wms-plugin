<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Repository;

use Setono\SyliusPeakWMSPlugin\Model\RegisteredWebhooksInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * @extends RepositoryInterface<RegisteredWebhooksInterface>
 */
interface RegisteredWebhooksRepositoryInterface extends RepositoryInterface
{
    public function findOneByVersion(string $version): ?RegisteredWebhooksInterface;
}
