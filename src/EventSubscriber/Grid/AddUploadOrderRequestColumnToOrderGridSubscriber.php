<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\EventSubscriber\Grid;

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
        $field = Field::fromNameAndType('peakWMSUploadOrderRequest', 'twig');
        $field->setOptions([
            'template' => '@SetonoSyliusPeakWMSPlugin/admin/order/grid/field/peak_wms_upload_order_request.html.twig',
            'vars' => [
                'labels' => '@SetonoSyliusPeakWMSPlugin/admin/order/grid/field/peak_wms_upload_order_request',
            ],
        ]);
        $field->setLabel('setono_sylius_peak_wms.ui.peak_wms_state');

        $event->getGrid()->addField($field);
    }
}
