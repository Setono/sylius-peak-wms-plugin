<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakPlugin\Functional\Controller;

use Setono\PeakWMS\Client\Client;
use Setono\PeakWMS\DataTransferObject\Stock\Stock;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderShippingStates;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\JsonMockResponse;

final class HandleWebhookActionTest extends WebTestCase
{
    private static KernelBrowser $client;

    protected function setUp(): void
    {
        self::$client = self::createClient();
    }

    /**
     * @test
     */
    public function it_handles_stock_adjustments(): void
    {
        $httpClient = new MockHttpClient([new JsonMockResponse([new Stock(
            productId: 'Everyday_white_basic_T_Shirt',
            variantId: 'Everyday_white_basic_T_Shirt-variant-0',
            quantity: 2,
            reservedQuantity: 0,
        )])]);

        $peakClient = self::getContainer()->get(Client::class);
        $peakClient->setHttpClient(new Psr18Client($httpClient));

        self::$client->request(
            method: 'POST',
            uri: '/peak/webhook?name=100',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'adjustedQuantity' => 0,
                'adjustmentReason' => 15,
                'productId' => 'Everyday_white_basic_T_Shirt', // Created by fixtures
                'variantId' => 'Everyday_white_basic_T_Shirt-variant-0',
                'quantity' => 2,
                'warehouseHostId' => '-1',
            ], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(204);

        // todo assert that the actual on hand value on the variant is changed
    }

    /**
     * @test
     */
    public function it_handles_order_packed(): void
    {
        $productVariant = $this->getProductVariant('Everyday_white_basic_T_Shirt-variant-0');

        $orderItem = $this->createOrderItem($productVariant);

        $order = $this->createOrder('USD', 'en_US');
        $order->setCheckoutCompletedAt(new \DateTimeImmutable());
        $order->setState(OrderInterface::STATE_NEW);
        $order->setCheckoutState(OrderCheckoutStates::STATE_COMPLETED);
        $order->setPaymentState(OrderPaymentStates::STATE_PAID);
        $order->setShippingState(OrderShippingStates::STATE_READY);
        $order->addItem($orderItem);

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = self::getContainer()->get('sylius.repository.order');
        $orderRepository->add($order);

        self::$client->request(
            method: 'POST',
            uri: '/peak/webhook?name=102',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: json_encode([
                'orderId' => (string) $order->getId(),
                'pickOrderId' => 123,
                'paymentCaptured' => true,
                'orderLines' => [
                    [
                        'orderLineId' => (string) $orderItem->getId(),
                        'quantity' => 1,
                    ],
                ],
            ], \JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(204);

        self::assertSame(OrderInterface::STATE_FULFILLED, $order->getState());
    }

    private function getProductVariant(string $code): ProductVariantInterface
    {
        /** @var ProductVariantRepositoryInterface $productVariantRepository */
        $productVariantRepository = self::getContainer()->get('sylius.repository.product_variant');

        $productVariant = $productVariantRepository->findOneBy(['code' => $code]);
        self::assertInstanceOf(ProductVariantInterface::class, $productVariant);

        return $productVariant;
    }

    private function createOrder(string $currencyCode, string $localeCode): OrderInterface
    {
        /** @var FactoryInterface $factory */
        $factory = self::getContainer()->get('sylius.factory.order');

        /** @var OrderInterface $order */
        $order = $factory->createNew();
        $order->setCurrencyCode($currencyCode);
        $order->setLocaleCode($localeCode);

        return $order;
    }

    private function createOrderItem(ProductVariantInterface $productVariant, int $quantity = 1): OrderItemInterface
    {
        /** @var FactoryInterface $factory */
        $factory = self::getContainer()->get('sylius.factory.order_item');

        /** @var OrderItemInterface $orderItem */
        $orderItem = $factory->createNew();
        $orderItem->setVariant($productVariant);

        /** @var OrderItemQuantityModifierInterface $orderItemQuantityModifier */
        $orderItemQuantityModifier = self::getContainer()->get('sylius.order_item_quantity_modifier');
        $orderItemQuantityModifier->modify($orderItem, $quantity);

        return $orderItem;
    }
}
