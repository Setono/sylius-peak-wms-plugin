<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\Command;

use Sylius\Component\Core\Model\ProductVariantInterface;

final class UpdateInventory implements CommandInterface
{
    public ?int $productVariant = null;

    private function __construct()
    {
    }

    public static function for(int|ProductVariantInterface $productVariant): self
    {
        if ($productVariant instanceof ProductVariantInterface) {
            $productVariant = (int) $productVariant->getId();
        }

        $command = new self();
        $command->productVariant = $productVariant;

        return $command;
    }

    public static function forAll(): self
    {
        return new self();
    }
}
