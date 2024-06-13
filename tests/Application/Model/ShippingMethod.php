<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakWMSPlugin\Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPeakWMSPlugin\Model\ShippingMethodInterface as PeakWMSShippingMethodInterface;
use Setono\SyliusPeakWMSPlugin\Model\ShippingMethodTrait as PeakWMSShippingMethodTrait;
use Sylius\Component\Core\Model\ShippingMethod as BaseShippingMethod;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="sylius_shipping_method")
 */
class ShippingMethod extends BaseShippingMethod implements PeakWMSShippingMethodInterface
{
    use PeakWMSShippingMethodTrait;
}
