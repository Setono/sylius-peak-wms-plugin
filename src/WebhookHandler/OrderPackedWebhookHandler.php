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
use Sylius\Component\Core\OrderShippingTransitions;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
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

        $orderShippingStateMachine = $this->stateMachineFactory->get($order, OrderShippingTransitions::GRAPH);

        if ($orderShippingStateMachine->can(OrderShippingTransitions::TRANSITION_SHIP)) {
            $this->logger->debug(sprintf('Taking the "%s" transition', OrderShippingTransitions::TRANSITION_SHIP));

            $orderShippingStateMachine->apply(OrderShippingTransitions::TRANSITION_SHIP);
        }

        if ($data->paymentCaptured) {
            $this->completePayment($order);
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
            $this->logger->debug(sprintf('Taking the "%s" transition', PaymentTransitions::TRANSITION_COMPLETE));

            $paymentStateMachine->apply(PaymentTransitions::TRANSITION_COMPLETE);
        }
    }
}
