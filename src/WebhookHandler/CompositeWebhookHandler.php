<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\WebhookHandler;

use Setono\CompositeCompilerPass\CompositeService;
use Setono\SyliusPeakWMSPlugin\Exception\UnsupportedWebhookException;

/**
 * @extends CompositeService<WebhookHandlerInterface>
 */
final class CompositeWebhookHandler extends CompositeService implements WebhookHandlerInterface
{
    public function handle(object $data): void
    {
        foreach ($this->services as $service) {
            if ($service->supports($data)) {
                $service->handle($data);
            }
        }

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
}
