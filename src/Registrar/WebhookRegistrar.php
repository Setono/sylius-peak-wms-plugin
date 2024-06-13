<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Registrar;

use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\Webhook\Name;
use Setono\PeakWMS\DataTransferObject\Webhook\Webhook;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class WebhookRegistrar implements WebhookRegistrarInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function register(): void
    {
        // todo delete all registered webhooks first

        foreach ($this->getWebhooks() as $webhook) {
            $this->client->webhook()->create($webhook);
        }
    }

    public function getVersion(): string
    {
        $webhooks = iterator_to_array($this->getWebhooks());
        usort($webhooks, static fn (Webhook $a, Webhook $b) => $a->name?->value <=> $b->name?->value);

        $webhooks = array_map(static fn (Webhook $webhook) => (string) $webhook->name?->value . (string) $webhook->url, $webhooks);

        return md5(implode('', $webhooks));
    }

    /**
     * @return \Generator<array-key, Webhook>
     */
    public function getWebhooks(): \Generator
    {
        foreach ([Name::StockAdjust, Name::PickOrderFullyPacked] as $name) {
            yield new Webhook(
                name: $name,
                url: $this->urlGenerator->generate(
                    name: 'setono_sylius_peak_wms_global_webhook',
                    referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            );
        }
    }
}
