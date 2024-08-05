<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakPlugin\Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPeakPlugin\Model\ProductVariantInterface as PeakProductVariantInterface;
use Setono\SyliusPeakPlugin\Model\ProductVariantTrait as PeakProductVariantTrait;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="sylius_product_variant")
 */
class ProductVariant extends BaseProductVariant implements PeakProductVariantInterface
{
    use PeakProductVariantTrait;
}
