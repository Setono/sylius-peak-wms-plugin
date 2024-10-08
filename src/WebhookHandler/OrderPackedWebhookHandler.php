<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\WebhookHandler;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataPickOrderLine;
use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataPickOrderPacked;
use Setono\SyliusPeakPlugin\Exception\UnsupportedWebhookException;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Order\OrderTransitions;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Shipping\ShipmentTransitions;
use Webmozart\Assert\Assert;

final class OrderPackedWebhookHandler implements WebhookHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly FactoryInterface $stateMachineFactory,
    ) {
        $this->logger = new NullLogger();
    }

    /**
     * @param object|WebhookDataPickOrderPacked $data
     */
    public function handle(object $data): void
    {
        if (!$this->supports($data)) {
            throw UnsupportedWebhookException::fromData($data);
        }

        $order = $this->orderRepository->find($data->orderId);
        if (!$order instanceof OrderInterface) {
            throw new \InvalidArgumentException(sprintf('Order with id "%s" not found', $data->orderId));
        }

        $this->logger->debug(sprintf('Order state before: %s', $order->getState()));
        $this->logger->debug(sprintf('Order checkout state before: %s', (string) $order->getCheckoutState()));
        $this->logger->debug(sprintf('Order shipping state state before: %s', (string) $order->getShippingState()));
        $this->logger->debug(sprintf('Order payment state before: %s', (string) $order->getPaymentState()));

        $syliusOrderLines = array_values(array_map(static fn (OrderItemInterface $orderItem): array => [
            'id' => (string) $orderItem->getId(),
            'quantity' => $orderItem->getQuantity(),
        ], $order->getItems()->toArray()));

        $peakOrderLines = array_map(static fn (WebhookDataPickOrderLine $orderLine): array => [
            'id' => $orderLine->orderLineId,
            'quantity' => $orderLine->quantity,
        ], $data->orderLines);

        try {
            self::assertSame($syliusOrderLines, $peakOrderLines);
        } catch (\InvalidArgumentException) {
            throw new \InvalidArgumentException(sprintf('Order lines on order %s are different', $data->orderId));
        }

        $this->completeShipment($order, $data);

        if ($data->paymentCaptured) {
            $this->completePayment($order);
        }

        $orderStateMachine = $this->stateMachineFactory->get($order, OrderTransitions::GRAPH);
        if ($orderStateMachine->can(OrderTransitions::TRANSITION_FULFILL)) {
            $this->logger->debug(sprintf('Order: Taking the "%s" transition', OrderTransitions::TRANSITION_FULFILL));

            $orderStateMachine->apply(OrderTransitions::TRANSITION_FULFILL);
        }

        $this->logger->debug(sprintf('Order state after: %s', $order->getState()));
        $this->logger->debug(sprintf('Order checkout state after: %s', (string) $order->getCheckoutState()));
        $this->logger->debug(sprintf('Order shipping state state after: %s', (string) $order->getShippingState()));
        $this->logger->debug(sprintf('Order payment state after: %s', (string) $order->getPaymentState()));

        $this->orderRepository->add($order);
    }

    /**
     * @psalm-assert-if-true WebhookDataPickOrderPacked $data
     */
    public function supports(object $data): bool
    {
        return $data instanceof WebhookDataPickOrderPacked;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @param list<array{id: string, quantity: int}> $syliusOrderLines
     * @param list<array{id: string, quantity: int}> $peakOrderLines
     */
    private static function assertSame(array $syliusOrderLines, array $peakOrderLines): void
    {
        foreach ($syliusOrderLines as $syliusOrderLine) {
            foreach ($peakOrderLines as $key => $peakOrderLine) {
                if ($peakOrderLine['id'] !== $syliusOrderLine['id']) {
                    continue;
                }

                if ($peakOrderLine['quantity'] === $syliusOrderLine['quantity']) {
                    unset($peakOrderLines[$key]);
                }
            }
        }

        Assert::count($peakOrderLines, 0);
    }

    private function completeShipment(OrderInterface $order, WebhookDataPickOrderPacked $data): void
    {
        $shipment = $order->getshipments()->last();
        if (false === $shipment) {
            $this->logger->debug('There is no shipment on the order');

            return;
        }

        $shipment->setTracking($data->trackingNumber);

        $shipmentStateMachine = $this->stateMachineFactory->get($shipment, ShipmentTransitions::GRAPH);

        if ($shipmentStateMachine->can(ShipmentTransitions::TRANSITION_SHIP)) {
            $this->logger->debug(sprintf('Shipment: Taking the "%s" transition', ShipmentTransitions::TRANSITION_SHIP));

            $shipmentStateMachine->apply(ShipmentTransitions::TRANSITION_SHIP);
        }
    }

    private function completePayment(OrderInterface $order): void
    {
        $this->logger->debug(sprintf('The payment is captured, so we will check if we can take the "%s" transition', PaymentTransitions::TRANSITION_COMPLETE));

        $payment = $order->getLastPayment();
        if (null === $payment) {
            $this->logger->debug(sprintf('There is no payment on order %s', (string) $order->getId()));

            return;
        }

        $paymentStateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        if ($paymentStateMachine->can(PaymentTransitions::TRANSITION_COMPLETE)) {
            $this->logger->debug(sprintf('Payment: Taking the "%s" transition', PaymentTransitions::TRANSITION_COMPLETE));

            $paymentStateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
        }
    }
}
