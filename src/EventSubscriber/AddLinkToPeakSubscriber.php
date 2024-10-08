<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber;

use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Sylius\Bundle\AdminBundle\Event\OrderShowMenuBuilderEvent;
use Sylius\Bundle\AdminBundle\Menu\OrderShowMenuBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AddLinkToPeakSubscriber implements EventSubscriberInterface
{
    private const MENU_ITEM_KEY = 'view_order_in_peak';

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

        $uploadOrderRequest = $order->getPeakUploadOrderRequest();
        if ($uploadOrderRequest === null) {
            return;
        }

        $peakOrderId = $uploadOrderRequest->getPeakOrderId();

        if ($uploadOrderRequest->getState() !== UploadOrderRequestInterface::STATE_UPLOADED || null === $peakOrderId) {
            return;
        }

        $menu = $event->getMenu();
        $sort = array_keys($menu->getChildren());
        array_unshift($sort, self::MENU_ITEM_KEY);

        $menu
            ->addChild(self::MENU_ITEM_KEY, [
                'uri' => sprintf('https://app%s.peakwms.com/dialog/orderOverview/%d/details', $this->testEnvironment ? '-test' : '', $peakOrderId),
            ])
            ->setAttribute('type', 'link')
            ->setLabel('setono_sylius_peak.ui.view_order_in_peak')
            ->setLabelAttribute('icon', 'external alternate')
            ->setLabelAttribute('color', 'blue')
        ;

        try {
            $event->getMenu()->reorderChildren($sort);
        } catch (\InvalidArgumentException) {
        }
    }
}
