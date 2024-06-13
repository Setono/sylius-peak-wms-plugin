<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakWMSPlugin\Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPeakWMSPlugin\Model\OrderInterface as PeakWMSOrderInterface;
use Setono\SyliusPeakWMSPlugin\Model\OrderTrait as PeakWMSOrderTrait;
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
