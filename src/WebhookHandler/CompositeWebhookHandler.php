<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\WebhookHandler;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\CompositeCompilerPass\CompositeService;
use Setono\SyliusPeakPlugin\Exception\UnsupportedWebhookException;

/**
 * @extends CompositeService<WebhookHandlerInterface>
 */
final class CompositeWebhookHandler extends CompositeService implements WebhookHandlerInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

    public function handle(object $data): void
    {
        $this->logger->debug(sprintf('Handling webhook %s', $data::class));

        foreach ($this->services as $service) {
            if ($service instanceof LoggerAwareInterface) {
                $service->setLogger($this->logger);
            }

            if ($service->supports($data)) {
                $service->handle($data);

                return;
            }
        }

        $this->logger->critical('The webhook was not supported by any of the webhook handlers');

        throw UnsupportedWebhookException::fromData($data);
    }

    public function supports(object $data): bool
    {
        foreach ($this->services as $service) {
            if ($service->supports($data)) {
                return true;
            }
        }

        return false;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
