<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\WebhookHandler;

use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataStockAdjust;
use Setono\SyliusPeakPlugin\Exception\UnsupportedWebhookException;
use Setono\SyliusPeakPlugin\Message\Command\UpdateInventory;
use Setono\SyliusPeakPlugin\Provider\ProductVariantProviderInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class StockAdjustmentWebhookHandler implements WebhookHandlerInterface
{
    public function __construct(
        private readonly ProductVariantProviderInterface $productVariantProvider,
        private readonly MessageBusInterface $commandBus,
    ) {
    }

    public function handle(object $data): void
    {
        if (!$this->supports($data)) {
            throw UnsupportedWebhookException::fromData($data);
        }

        $productVariant = $this->productVariantProvider->provideFromStockAdjustment($data);
        if (null === $productVariant) {
            throw new \InvalidArgumentException(sprintf('Product variant with id/code "%s" not found', (string) $data->variantId));
        }

        $this->commandBus->dispatch(new UpdateInventory($productVariant));
    }

    /**
     * @psalm-assert-if-true WebhookDataStockAdjust $data
     */
    public function supports(object $data): bool
    {
        return $data instanceof WebhookDataStockAdjust;
    }
}
