<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Setono\SyliusPeakPlugin\Model\InventoryUpdateInterface;

interface InventoryUpdateProviderInterface
{
    /**
     * Will return an inventory update entity. If one doesn't exist in the database yet, this provider will create it
     */
    public function getInventoryUpdate(): InventoryUpdateInterface;
}
