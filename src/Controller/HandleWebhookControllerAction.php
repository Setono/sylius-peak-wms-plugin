<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Controller;

use Setono\PeakWMS\Parser\WebhookParser;
use Setono\PeakWMS\Parser\WebhookParserInterface;
use Setono\SyliusPeakWMSPlugin\WebhookHandler\WebhookHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class HandleWebhookControllerAction
{
    public function __construct(
        private readonly WebhookParserInterface $webhookParser,
        private readonly WebhookHandlerInterface $webhookHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $dataClass = WebhookParser::convertNameToDataClass($request->query->getInt('name'));

            $data = $this->webhookParser->parse($request->getContent(), $dataClass);

            $this->webhookHandler->handle($data);
        } catch (\InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(status: Response::HTTP_NO_CONTENT);
    }
}
