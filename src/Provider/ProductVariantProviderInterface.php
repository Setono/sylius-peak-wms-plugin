<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Setono\PeakWMS\DataTransferObject\Webhook\WebhookDataStockAdjust;
use Sylius\Component\Core\Model\ProductVariantInterface;

interface ProductVariantProviderInterface
{
    public function provideFromStockAdjustment(WebhookDataStockAdjust $stockAdjustment): ?ProductVariantInterface;
}
