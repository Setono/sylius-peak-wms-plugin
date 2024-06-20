<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber;

use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AddMenuSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.menu.admin.main' => 'add',
        ];
    }

    public function add(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $subMenu = $menu->getChild('configuration');

        if (null !== $subMenu) {
            $this->addChild($subMenu);
        } else {
            $this->addChild($menu->getFirstChild());
        }
    }

    private function addChild(ItemInterface $item): void
    {
        $item
            ->addChild('setono_sylius_peak', [
                'route' => 'setono_sylius_peak_admin_peak_index',
            ])
            ->setLabel('setono_sylius_peak.ui.peak')
            ->setLabelAttribute('icon', 'truck')
        ;
    }
}
