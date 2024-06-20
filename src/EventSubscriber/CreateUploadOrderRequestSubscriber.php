<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Factory\UploadOrderRequestFactoryInterface;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

final class CreateUploadOrderRequestSubscriber implements EventSubscriberInterface
{
    use ORMTrait;

    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly UploadOrderRequestFactoryInterface $uploadOrderRequestFactory,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order.pre_complete' => 'createUploadOrderRequest',
        ];
    }

    public function createUploadOrderRequest(ResourceControllerEvent $event): void
    {
        /** @var OrderInterface|mixed $order */
        $order = $event->getSubject();
        Assert::isInstanceOf($order, OrderInterface::class);

        $order->setPeakUploadOrderRequest($this->uploadOrderRequestFactory->createNew());
    }
}
