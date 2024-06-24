<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin;

use Setono\CompositeCompilerPass\CompositeCompilerPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Sylius\Bundle\ResourceBundle\AbstractResourceBundle;
use Sylius\Bundle\ResourceBundle\SyliusResourceBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/** @psalm-suppress DeprecatedInterface */
final class SetonoSyliusPeakPlugin extends AbstractResourceBundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CompositeCompilerPass(
            'setono_sylius_peak.data_mapper.sales_order.composite',
            'setono_sylius_peak.sales_order_data_mapper',
        ));

        $container->addCompilerPass(new CompositeCompilerPass(
            'setono_sylius_peak.webhook_handler.composite',
            'setono_sylius_peak.webhook_handler',
        ));
    }

    public function getSupportedDrivers(): array
    {
        return [
            SyliusResourceBundle::DRIVER_DOCTRINE_ORM,
        ];
    }
}