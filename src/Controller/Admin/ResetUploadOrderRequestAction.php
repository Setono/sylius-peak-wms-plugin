<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Controller\Admin;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Workflow\WorkflowInterface;

final class ResetUploadOrderRequestAction
{
    use ORMTrait;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly WorkflowInterface $uploadOrderRequestWorkflow,
        private readonly UrlGeneratorInterface $urlGenerator,
        /** @var class-string<UploadOrderRequestInterface> $uploadOrderRequestClass */
        private readonly string $uploadOrderRequestClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(Request $request, int $id): RedirectResponse
    {
        $manager = $this->getManager($this->uploadOrderRequestClass);

        /** @var UploadOrderRequestInterface|null $uploadOrderRequest */
        $uploadOrderRequest = $manager->find($this->uploadOrderRequestClass, $id);
        if ($uploadOrderRequest === null) {
            throw new NotFoundHttpException(sprintf('Upload order request with id %d not found', $id));
        }

        if ($this->uploadOrderRequestWorkflow->can($uploadOrderRequest, UploadOrderRequestWorkflow::TRANSITION_RESET)) {
            $this->uploadOrderRequestWorkflow->apply($uploadOrderRequest, UploadOrderRequestWorkflow::TRANSITION_RESET);
        }

        $manager->flush();

        $session = $request->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add('success', 'setono_sylius_peak.upload_order_request_reset');
        }

        $referrer = $request->headers->get('referer');
        if (is_string($referrer)) {
            return new RedirectResponse($referrer);
        }

        return new RedirectResponse($this->urlGenerator->generate('sylius_admin_order_show', [
            'id' => $uploadOrderRequest->getOrder()?->getId(),
        ]));
    }
}
