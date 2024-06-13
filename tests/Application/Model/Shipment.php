<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakWMSPlugin\Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPeakWMSPlugin\Model\ShipmentInterface;
use Setono\SyliusPeakWMSPlugin\Model\ShipmentTrait;
use Sylius\Component\Core\Model\Shipment as BaseShipment;

/**
 * @ORM\Entity()
 *
 * @ORM\Table(name="sylius_shipment")
 */
class Shipment extends BaseShipment implements ShipmentInterface
{
    use ShipmentTrait;
}
