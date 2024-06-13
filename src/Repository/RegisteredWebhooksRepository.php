<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Repository;

use Setono\SyliusPeakWMSPlugin\Model\RegisteredWebhooksInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Webmozart\Assert\Assert;

class RegisteredWebhooksRepository extends EntityRepository implements RegisteredWebhooksRepositoryInterface
{
    public function findOneByVersion(string $version): ?RegisteredWebhooksInterface
    {
        $obj = $this->findOneBy(['version' => $version]);
        Assert::nullOrIsInstanceOf($obj, RegisteredWebhooksInterface::class);

        return $obj;
    }
}
