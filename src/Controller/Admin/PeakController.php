<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Controller\Admin;

use Setono\PeakWMS\Client\ClientInterface;
use Setono\SyliusPeakPlugin\Message\Command\RegisterWebhooks;
use Setono\SyliusPeakPlugin\Registrar\WebhookRegistrarInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final class PeakController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly WebhookRegistrarInterface $webhookRegistrar,
        private readonly ClientInterface $client,
    ) {
    }

    public function index(): Response
    {
        $apiError = false;

        try {
            $this->client->ping();
        } catch (\Throwable) {
            $apiError = 'There was an error connecting to the API. Please check your credentials and try again. If you continue to experience issues, did you forget to enable/disable the sandbox?';
        }

        return $this->render('@SetonoSyliusPeakPlugin/admin/peak_wms/index.html.twig', [
            'apiError' => $apiError,
            'webhooksShouldBeRegistered' => $this->webhookRegistrar->outOfDate(),
        ]);
    }

    public function registerWebhooks(): RedirectResponse
    {
        $this->commandBus->dispatch(new RegisterWebhooks());

        $this->addFlash('success', 'Webhooks registered successfully');

        return $this->redirectToRoute('setono_sylius_peak_wms_admin_peak_wms_index');
    }
}
