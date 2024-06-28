<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Command;

use Setono\SyliusPeakPlugin\Updater\InventoryUpdaterInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'setono:sylius-peak-wms:update-inventory',
    description: 'This will update the inventory for all product variants',
)]
final class UpdateInventoryCommand extends Command
{
    public function __construct(private readonly InventoryUpdaterInterface $inventoryUpdater)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // todo allow the user to specify a product variant id to update
        // todo allow the user to force the update of _ALL_ product variants regardless of the last update time
        $this->inventoryUpdater->updateAll();

        return 0;
    }
}
