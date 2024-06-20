<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Message\CommandHandler;

use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\Product\Product;
use Setono\SyliusPeakPlugin\Message\Command\UpdateInventory;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Core\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final class UpdateInventoryHandler
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
    ) {
    }

    public function __invoke(UpdateInventory $message): void
    {
        $productVariant = $this->productVariantRepository->find($message->productVariant);
        if (!$productVariant instanceof ProductVariantInterface) {
            throw new UnrecoverableMessageHandlingException(sprintf('Product variant with id %d not found', $message->productVariant));
        }

        $productCode = $productVariant->getProduct()?->getCode();
        $variantCode = $productVariant->getCode();

        if (null === $productCode || null === $variantCode) {
            throw new UnrecoverableMessageHandlingException(sprintf('Product variant with id %d does not have a product code or variant code', $message->productVariant));
        }

        $collection = $this
            ->client
            ->product()
            ->getByProductId($productCode)
            ->filter(fn (Product $product) => $product->variantId === $variantCode)
        ;

        if (count($collection) === 0) {
            throw new UnrecoverableMessageHandlingException(sprintf('The product with id %s does not have a variant with id/code %s', $productCode, $variantCode));
        }

        if (count($collection) > 1) {
            throw new UnrecoverableMessageHandlingException(sprintf('The product with id %s has multiple products with the same variant id/code', $productCode));
        }

        $peakProduct = $collection[0];

        if (null === $peakProduct->availableToSell) {
            throw new UnrecoverableMessageHandlingException(sprintf('The product with id %s and variant id/code %s does not have an availableToSell value', $productCode, $variantCode));
        }

        $productVariant->setOnHand($peakProduct->availableToSell);

        $this->productVariantRepository->add($productVariant);
    }
}
