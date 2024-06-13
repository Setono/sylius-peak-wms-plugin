<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Message\CommandHandler;

use Setono\SyliusPeakWMSPlugin\Message\Command\RegisterWebhooks;
use Setono\SyliusPeakWMSPlugin\Model\RegisteredWebhooksInterface;
use Setono\SyliusPeakWMSPlugin\Registrar\WebhookRegistrarInterface;
use Setono\SyliusPeakWMSPlugin\Repository\RegisteredWebhooksRepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class RegisterWebhooksHandler
{
    public function __construct(
        private readonly RegisteredWebhooksRepositoryInterface $registeredWebhooksRepository,
        private readonly WebhookRegistrarInterface $webhookRegistrar,
        /** @var FactoryInterface<RegisteredWebhooksInterface> $registeredWebhooksFactory */
        private readonly FactoryInterface $registeredWebhooksFactory,
    ) {
    }

    public function __invoke(RegisterWebhooks $message): void
    {
        $this->webhookRegistrar->register();

        $version = $this->webhookRegistrar->getVersion();

        $registeredWebhooks = $this->registeredWebhooksRepository->findOneByVersion($version);
        if (null === $registeredWebhooks) {
            $registeredWebhooks = $this->registeredWebhooksFactory->createNew();
        }

        $registeredWebhooks->setRegisteredAt(new \DateTimeImmutable());
        $registeredWebhooks->setVersion($this->webhookRegistrar->getVersion());

        $this->registeredWebhooksRepository->add($registeredWebhooks);
    }
}
