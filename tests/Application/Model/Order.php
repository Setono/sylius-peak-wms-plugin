<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakPlugin\Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPeakPlugin\Model\OrderInterface as PeakWMSOrderInterface;
use Setono\SyliusPeakPlugin\Model\OrderTrait as PeakWMSOrderTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="sylius_order")
 */
class Order extends BaseOrder implements PeakWMSOrderInterface
{
    use PeakWMSOrderTrait;
}
