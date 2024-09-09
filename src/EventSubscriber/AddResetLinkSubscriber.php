<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber;

use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Sylius\Bundle\AdminBundle\Event\OrderShowMenuBuilderEvent;
use Sylius\Bundle\AdminBundle\Menu\OrderShowMenuBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\WorkflowInterface;

final class AddResetLinkSubscriber implements EventSubscriberInterface
{
    private const MENU_ITEM_KEY = 'reset_upload_order_request';

    public function __construct(private readonly WorkflowInterface $workflow)
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

        if (!$this->workflow->can($uploadOrderRequest, UploadOrderRequestWorkflow::TRANSITION_RESET)) {
            return;
        }

        $menu = $event->getMenu();
        $sort = array_keys($menu->getChildren());

        $menu
            ->addChild(self::MENU_ITEM_KEY, [
                'route' => 'setono_sylius_peak_admin_reset_upload_order_request',
                'routeParameters' => ['id' => $uploadOrderRequest->getId()],
            ])
            ->setAttribute('type', 'link')
            ->setLabel($uploadOrderRequest->getPeakOrderId() === null ? 'setono_sylius_peak.ui.upload_order_to_peak' : 'setono_sylius_peak.ui.re_upload_order_to_peak')
            ->setLabelAttribute('icon', 'redo')
        ;

        array_unshift($sort, self::MENU_ITEM_KEY);

        try {
            $event->getMenu()->reorderChildren($sort);
        } catch (\InvalidArgumentException) {
        }
    }
}
