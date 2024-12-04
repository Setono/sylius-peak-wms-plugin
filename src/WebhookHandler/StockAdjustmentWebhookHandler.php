<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\WebhookHandler;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataStockAdjust;
use Setono\SyliusPeakPlugin\Exception\UnsupportedWebhookException;
use Setono\SyliusPeakPlugin\Provider\ProductVariantProviderInterface;
use Setono\SyliusPeakPlugin\Updater\InventoryUpdaterInterface;

final class StockAdjustmentWebhookHandler implements WebhookHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly ProductVariantProviderInterface $productVariantProvider,
        private readonly InventoryUpdaterInterface $inventoryUpdater,
    ) {
        $this->logger = new NullLogger();
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

        $this->inventoryUpdater->update($productVariant);

        $this->logger->debug(sprintf('Updated inventory for product variant %s', (string) $productVariant->getCode()));
    }

    /**
     * @psalm-assert-if-true WebhookDataStockAdjust $data
     */
    public function supports(object $data): bool
    {
        return $data instanceof WebhookDataStockAdjust;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
