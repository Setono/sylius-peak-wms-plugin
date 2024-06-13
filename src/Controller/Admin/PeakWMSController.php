<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Controller\Admin;

use Setono\SyliusPeakWMSPlugin\Message\Command\RegisterWebhooks;
use Setono\SyliusPeakWMSPlugin\Registrar\WebhookRegistrarInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

final class PeakWMSController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly WebhookRegistrarInterface $webhookRegistrar,
    ) {
    }

    public function index(): Response
    {
        return $this->render('@SetonoSyliusPeakWMSPlugin/admin/peak_wms/index.html.twig', [
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
