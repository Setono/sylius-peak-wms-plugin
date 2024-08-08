<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DataMapper\SalesOrder;

use Setono\PeakWMS\DataTransferObject\Address;
use Setono\PeakWMS\DataTransferObject\SalesOrder\SalesOrder;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ShippingMethodInterface;
use Webmozart\Assert\Assert;

final class SalesOrderDataMapper implements SalesOrderDataMapperInterface
{
    public function map(OrderInterface $order, SalesOrder $salesOrder): void
    {
        $salesOrder->orderId = (string) $order->getId();
        $salesOrder->orderNumber = (string) $order->getNumber();
        $salesOrder->orderDateTime = SalesOrder::convertDateTime($order->getCheckoutCompletedAt());
        $salesOrder->comment = $order->getNotes();

        $salesOrder->shippingAddress = new Address(
            customerName: $order->getShippingAddress()?->getFullName(),
            address1: $order->getShippingAddress()?->getStreet(),
            postalCode: $order->getShippingAddress()?->getPostcode(),
            city: $order->getShippingAddress()?->getCity(),
            country: $order->getShippingAddress()?->getCountryCode(),
            email: $order->getCustomer()?->getEmail(),
            phone: $order->getShippingAddress()?->getPhoneNumber(),
        );

        $salesOrder->billingAddress = new Address(
            customerName: $order->getBillingAddress()?->getFullName(),
            address1: $order->getBillingAddress()?->getStreet(),
            postalCode: $order->getBillingAddress()?->getPostcode(),
            city: $order->getBillingAddress()?->getCity(),
            country: $order->getBillingAddress()?->getCountryCode(),
            email: $order->getCustomer()?->getEmail(),
            phone: $order->getBillingAddress()?->getPhoneNumber(),
        );

        // todo set discount cost

        $salesOrder->paymentMethod = self::getPaymentMethod($order)?->getName();
        $salesOrder->forwarderProductId = self::getShippingMethod($order)?->getCode();
    }

    private static function getPaymentMethod(OrderInterface $order): ?PaymentMethodInterface
    {
        $paymentMethod = null;
        $payment = $order->getPayments()->first();
        if (false !== $payment) {
            $paymentMethod = $payment->getMethod();
        }
        Assert::nullOrIsInstanceOf($paymentMethod, PaymentMethodInterface::class);

        return $paymentMethod;
    }

    private static function getShippingMethod(OrderInterface $order): ?ShippingMethodInterface
    {
        $shippingMethod = null;
        $shipment = $order->getShipments()->first();
        if (false !== $shipment) {
            $shippingMethod = $shipment->getMethod();
        }
        Assert::nullOrIsInstanceOf($shippingMethod, ShippingMethodInterface::class);

        return $shippingMethod;
    }
}
