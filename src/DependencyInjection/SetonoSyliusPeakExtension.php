<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\DependencyInjection;

use Setono\SyliusPeakPlugin\DataMapper\SalesOrder\SalesOrderDataMapperInterface;
use Setono\SyliusPeakPlugin\WebhookHandler\WebhookHandlerInterface;
use Setono\SyliusPeakPlugin\Workflow\InventoryUpdateWorkflow;
use Setono\SyliusPeakPlugin\Workflow\UploadOrderRequestWorkflow;
use Setono\SyliusPeakPlugin\Workflow\UploadProductVariantRequestWorkflow;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoSyliusPeakExtension extends AbstractResourceExtension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         *
         * @var array{api_key: string, resources: array} $config
         */
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container
            ->registerForAutoconfiguration(SalesOrderDataMapperInterface::class)
            ->addTag('setono_sylius_peak.sales_order_data_mapper')
        ;

        $container
            ->registerForAutoconfiguration(WebhookHandlerInterface::class)
            ->addTag('setono_sylius_peak.webhook_handler')
        ;

        $container->setParameter('setono_sylius_peak.api_key', $config['api_key']);

        $this->registerResources(
            'setono_sylius_peak',
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
            $config['resources'],
            $container,
        );

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'buses' => [
                    'setono_sylius_peak.command_bus' => [
                        'middleware' => null,
                    ],
                ],
            ],
            'workflows' => UploadOrderRequestWorkflow::getConfig() + UploadProductVariantRequestWorkflow::getConfig() + InventoryUpdateWorkflow::getConfig(),
        ]);
    }
}
