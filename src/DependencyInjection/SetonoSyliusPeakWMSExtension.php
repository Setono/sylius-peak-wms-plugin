<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\DependencyInjection;

use Setono\SyliusPeakWMSPlugin\DataMapper\SalesOrderDataMapperInterface;
use Setono\SyliusPeakWMSPlugin\Workflow\UploadOrderRequestWorkflow;
use Sylius\Bundle\ResourceBundle\DependencyInjection\Extension\AbstractResourceExtension;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoSyliusPeakWMSExtension extends AbstractResourceExtension implements PrependExtensionInterface
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
            ->addTag('setono_sylius_peak_wms.sales_order_data_mapper')
        ;

        $container->setParameter('setono_sylius_peak_wms.api_key', $config['api_key']);

        $this->registerResources(
            'setono_sylius_peak_wms',
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
                    'setono_sylius_peak_wms.command_bus' => [
                        'middleware' => [
                            'doctrine_transaction',
                            'router_context',
                        ],
                    ],
                ],
            ],
            'workflows' => UploadOrderRequestWorkflow::getConfig(),
        ]);
    }
}
