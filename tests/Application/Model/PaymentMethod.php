<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakWMSPlugin\Application\Model;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPeakWMSPlugin\Model\PaymentMethodInterface as PeakWMSPaymentMethodInterface;
use Setono\SyliusPeakWMSPlugin\Model\PaymentMethodTrait as PeakWMSPaymentMethodTrait;
use Sylius\Component\Core\Model\PaymentMethod as BasePaymentMethod;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="sylius_payment_method")
 */
class PaymentMethod extends BasePaymentMethod implements PeakWMSPaymentMethodInterface
{
    use PeakWMSPaymentMethodTrait;
}
