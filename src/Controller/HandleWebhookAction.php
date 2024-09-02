<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerAwareInterface;
use Setono\Doctrine\ORMTrait;
use Setono\PeakWMS\Parser\WebhookParser;
use Setono\PeakWMS\Parser\WebhookParserInterface;
use Setono\SyliusPeakPlugin\Event\WebhookHandledEvent;
use Setono\SyliusPeakPlugin\Factory\WebhookFactoryInterface;
use Setono\SyliusPeakPlugin\Logger\WebhookLogger;
use Setono\SyliusPeakPlugin\WebhookHandler\WebhookHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class HandleWebhookAction
{
    use ORMTrait;

    public function __construct(
        private readonly WebhookParserInterface $webhookParser,
        private readonly WebhookHandlerInterface $webhookHandler,
        private readonly WebhookFactoryInterface $webhookFactory,
        ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $webhook = null;

        try {
            $webhook = $this->webhookFactory->createFromRequest($request);
            $logger = new WebhookLogger($webhook);

            if ($this->webhookHandler instanceof LoggerAwareInterface) {
                $this->webhookHandler->setLogger($logger);
            }

            $dataClass = WebhookParser::convertNameToDataClass($request->query->getInt('name'));

            $data = $this->webhookParser->parse($request->getContent(), $dataClass);

            $this->webhookHandler->handle($data);

            $this->eventDispatcher->dispatch(new WebhookHandledEvent($webhook));
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } finally {
            if (null !== $webhook) {
                $this->getManager($webhook)->persist($webhook);
                $this->getManager($webhook)->flush();
            }
        }

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
