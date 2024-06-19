<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\EventSubscriber;

use Setono\SyliusPeakWMSPlugin\Model\OrderInterface;
use Setono\SyliusPeakWMSPlugin\Model\UploadOrderRequestInterface;
use Sylius\Bundle\AdminBundle\Event\OrderShowMenuBuilderEvent;
use Sylius\Bundle\AdminBundle\Menu\OrderShowMenuBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AddLinkToPeakSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly bool $testEnvironment)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [OrderShowMenuBuilder::EVENT_NAME => 'addLink'];
    }

    public function addLink(OrderShowMenuBuilderEvent $event): void
    {
        $order = $event->getOrder();
        if (!$order instanceof OrderInterface) {
            return;
        }

        $uploadOrderRequest = $order->getPeakWMSUploadOrderRequest();
        if ($uploadOrderRequest === null) {
            return;
        }

        $peakOrderId = $uploadOrderRequest->getPeakOrderId();

        if ($uploadOrderRequest->getState() !== UploadOrderRequestInterface::STATE_UPLOADED || null === $peakOrderId) {
            return;
        }

        $menu = $event->getMenu();
        $menu
            ->addChild('view_order_in_peak', [
                'uri' => sprintf('https://app%s.peakwms.com/dialog/orderOverview/%d/details', $this->testEnvironment ? '-test' : '', $peakOrderId),
            ])
            ->setAttribute('type', 'link')
            ->setLabel('setono_sylius_peak_wms.ui.view_order_in_peak')
            ->setLabelAttribute('icon', 'external alternate')
            ->setLabelAttribute('color', 'blue')
        ;

        $menu->reorderChildren(['view_order_in_peak', 'order_history', 'cancel']);
    }
}
