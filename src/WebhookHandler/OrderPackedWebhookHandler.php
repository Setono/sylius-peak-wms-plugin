<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\WebhookHandler;

use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataPickOrderLine;
use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataPickOrderPacked;
use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataStockAdjust;
use Setono\SyliusPeakWMSPlugin\Exception\UnsupportedWebhookException;
use Setono\SyliusPeakWMSPlugin\Model\OrderInterface;
use SM\Factory\FactoryInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Core\OrderShippingTransitions;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Webmozart\Assert\Assert;

final class OrderPackedWebhookHandler implements WebhookHandlerInterface
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly FactoryInterface $stateMachineFactory,
    ) {
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

        $syliusOrderLines = array_values(array_map(static function (OrderItemInterface $orderItem): array {
            return [
                'id' => (string) $orderItem->getId(),
                'quantity' => $orderItem->getQuantity(),
            ];
        }, $order->getItems()->toArray()));

        $peakOrderLines = array_map(static function (WebhookDataPickOrderLine $orderLine): array {
            return [
                'id' => $orderLine->orderLineId,
                'quantity' => $orderLine->quantity,
            ];
        }, $data->orderLines);

        try {
            self::assertSame($syliusOrderLines, $peakOrderLines);
        } catch (\InvalidArgumentException) {
            throw new \InvalidArgumentException(sprintf('Order lines on order %s are different', $data->orderId));
        }

        $orderShippingStateMachine = $this->stateMachineFactory->get($order, OrderShippingTransitions::GRAPH);

        if ($orderShippingStateMachine->can(OrderShippingTransitions::TRANSITION_SHIP)) {
            $orderShippingStateMachine->apply(OrderShippingTransitions::TRANSITION_SHIP);
        }

        if ($data->paymentCaptured) {
            $orderPaymentStateMachine = $this->stateMachineFactory->get($order, OrderPaymentTransitions::GRAPH);

            if ($orderPaymentStateMachine->can(OrderPaymentTransitions::TRANSITION_PAY)) {
                $orderPaymentStateMachine->apply(OrderPaymentTransitions::TRANSITION_PAY);
            }
        }

        $this->orderRepository->add($order);
    }

    /**
     * @psalm-assert-if-true WebhookDataPickOrderPacked $data
     */
    public function supports(object $data): bool
    {
        return $data instanceof WebhookDataStockAdjust;
    }

    /**
     * @param list<array{id: string, quantity: int}> $syliusOrderLines
     * @param list<array{id: string, quantity: int}> $peakOrderLines
     */
    private static function assertSame(array $syliusOrderLines, array $peakOrderLines): void
    {
        foreach ($syliusOrderLines as $syliusOrderLine) {
            foreach ($peakOrderLines as $key => $peakOrderLine) {
                if ($peakOrderLine['id'] === $syliusOrderLine['id']) {
                    continue;
                }

                if ($peakOrderLine['quantity'] === $syliusOrderLine['quantity']) {
                    unset($peakOrderLines[$key]);
                }
            }
        }

        Assert::count($peakOrderLines, 0);
    }
}
