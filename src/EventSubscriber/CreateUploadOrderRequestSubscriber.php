<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\EventSubscriber;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakWMSPlugin\Factory\UploadOrderRequestFactoryInterface;
use Setono\SyliusPeakWMSPlugin\Model\OrderInterface;
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

        $order->setPeakWMSUploadOrderRequest($this->uploadOrderRequestFactory->createNew());
    }
}
