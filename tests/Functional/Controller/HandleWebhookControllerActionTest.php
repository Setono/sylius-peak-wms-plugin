<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakPlugin\Functional\Controller;

use Setono\SyliusPeakPlugin\Message\Command\UpdateInventory;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

final class HandleWebhookControllerActionTest extends WebTestCase
{
    use InteractsWithMessenger;

    /**
     * @test
     */
    public function it_handles_stock_adjustments(): void
    {
        $client = static::createClient();

        $client->request(
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

        /** @var list<UpdateInventory> $messages */
        $messages = $this->transport('async')->queue()->messages(UpdateInventory::class);
        self::assertCount(1, $messages);

        self::assertSame($this->getProductVariantId('Everyday_white_basic_T_Shirt-variant-0'), $messages[0]->productVariant);
    }

    private function getProductVariantId(string $code): int
    {
        /** @var ProductVariantRepositoryInterface $productVariantRepository */
        $productVariantRepository = self::getContainer()->get('sylius.repository.product_variant');

        $productVariant = $productVariantRepository->findOneBy(['code' => $code]);
        self::assertInstanceOf(ProductVariantInterface::class, $productVariant);

        return (int) $productVariant->getId();
    }
}
