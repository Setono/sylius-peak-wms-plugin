<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Registrar;

use Setono\PeakWMS\Client\ClientInterface;
use Setono\PeakWMS\DataTransferObject\Webhook\Name;
use Setono\PeakWMS\DataTransferObject\Webhook\Webhook;
use Setono\SyliusPeakPlugin\Exception\WebhookRegistrationException;
use Setono\SyliusPeakPlugin\Factory\RegisteredWebhooksFactoryInterface;
use Setono\SyliusPeakPlugin\Model\RegisteredWebhooksInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class WebhookRegistrar implements WebhookRegistrarInterface
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly RepositoryInterface $registeredWebhooksRepository,
        private readonly RegisteredWebhooksFactoryInterface $registeredWebhooksFactory,
    ) {
    }

    public function register(): void
    {
        /**
         * This will delete all webhooks registered with Peak WMS and also remove the logs from the database
         *
         * @var RegisteredWebhooksInterface $registeredWebhooks
         */
        foreach ($this->registeredWebhooksRepository->findAll() as $registeredWebhooks) {
            /** @var mixed $webhook */
            foreach ($registeredWebhooks->getWebhooks() as $webhook) {
                if (!is_array($webhook) || !isset($webhook['id']) || !is_int($webhook['id'])) {
                    throw new WebhookRegistrationException('The webhooks are not in the correct format');
                }

                $this->client->webhook()->delete($webhook['id']);
            }

            $this->registeredWebhooksRepository->remove($registeredWebhooks);
        }

        $postedWebhooks = [];

        foreach ($this->getWebhooks() as $webhook) {
            $postedWebhooks[] = $this->client->webhook()->create($webhook);
        }

        $registeredWebhooks = $this->registeredWebhooksFactory->createFromData($this->getVersion(), $postedWebhooks);
        $this->registeredWebhooksRepository->add($registeredWebhooks);
    }

    public function outOfDate(): bool
    {
        /** @var list<RegisteredWebhooksInterface> $registeredWebhooks */
        $registeredWebhooks = $this->registeredWebhooksRepository->findAll();
        if (count($registeredWebhooks) !== 1) {
            // We should only have one registered webhooks object. If we have more, it's a bug, and we consider it out of date. If we have none, we consider it out of date because they need to be registered.
            return true;
        }

        return $registeredWebhooks[0]->getVersion() !== $this->getVersion();
    }

    private function getVersion(): string
    {
        $webhooks = $this->getWebhooks();
        usort($webhooks, static fn (Webhook $a, Webhook $b) => $a->name?->value <=> $b->name?->value);

        $webhooks = array_map(static fn (Webhook $webhook) => (string) $webhook->name?->value . (string) $webhook->url, $webhooks);

        return md5(implode('', $webhooks));
    }

    /**
     * @return list<Webhook>
     */
    private function getWebhooks(): array
    {
        $webhooks = [];

        foreach ([Name::StockAdjust, Name::PickOrderPacked] as $name) {
            $webhooks[] = new Webhook(
                name: $name,
                url: $this->urlGenerator->generate(
                    name: 'setono_sylius_peak_wms_global_webhook',
                    parameters: ['name' => $name],
                    referenceType: UrlGeneratorInterface::ABSOLUTE_URL,
                ),
            );
        }

        return $webhooks;
    }
}
