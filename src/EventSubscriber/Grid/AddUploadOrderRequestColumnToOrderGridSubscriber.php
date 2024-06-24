<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber\Grid;

use Sylius\Component\Grid\Definition\Field;
use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AddUploadOrderRequestColumnToOrderGridSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.grid.admin_order' => 'add',
        ];
    }

    public function add(GridDefinitionConverterEvent $event): void
    {
        $field = Field::fromNameAndType('peakUploadOrderRequest', 'twig');
        $field->setOptions([
            'template' => '@SetonoSyliusPeakPlugin/admin/order/grid/field/peak_upload_order_request.html.twig',
            'vars' => [
                'labels' => '@SetonoSyliusPeakPlugin/admin/order/grid/field/peak_upload_order_request',
            ],
        ]);
        $field->setLabel('setono_sylius_peak.ui.peak_state');

        $event->getGrid()->addField($field);
    }
}
