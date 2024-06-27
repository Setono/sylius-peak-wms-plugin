<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Model\InventoryUpdateInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final class InventoryUpdateProvider implements InventoryUpdateProviderInterface
{
    use ORMTrait;

    public function __construct(ManagerRegistry $managerRegistry, private readonly FactoryInterface $factory)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function getInventoryUpdate(): InventoryUpdateInterface
    {
        $inventoryUpdates = $this->managerRegistry->getRepository(InventoryUpdateInterface::class)->findAll();
        $c = count($inventoryUpdates);

        if ($c > 1) {
            throw new \RuntimeException(sprintf(
                'Expected to find zero or one inventory update, but found %d',
                $c,
            ));
        }

        if ($c === 1) {
            return $inventoryUpdates[0];
        }

        $inventoryUpdate = $this->factory->createNew();
        Assert::isInstanceOf($inventoryUpdate, InventoryUpdateInterface::class);

        return $inventoryUpdate;
    }
}
