<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakPlugin\Application;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    protected function build(ContainerBuilder $container): void
    {
        // this compiler pass is added to set the base uri in the router context to that of the tunnel of expose
        $container->addCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container): void
            {
                if ($container->getParameter('kernel.environment') !== 'dev') {
                    return;
                }

                $httpClient = HttpClient::create();

                try {
                    $data = $httpClient->request('GET', 'http://127.0.0.1:4040/api/tunnels')->toArray();
                } catch (\Throwable) {
                    return;
                }

                if (isset($data['tunnels']) && is_array($data['tunnels']) && array_is_list($data['tunnels']) && count($data['tunnels']) > 0) {
                    $container->getDefinition('router.request_context')->replaceArgument(0, $data['tunnels'][0]);
                }
            }
        });
    }

    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    public function registerBundles(): iterable
    {
        foreach ($this->getConfigurationDirectories() as $confDir) {
            $bundlesFile = $confDir . '/bundles.php';
            if (false === is_file($bundlesFile)) {
                continue;
            }
            yield from $this->registerBundlesFromFile($bundlesFile);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        foreach ($this->getConfigurationDirectories() as $confDir) {
            $this->loadRoutesConfiguration($routes, $confDir);
        }
    }

    private function loadRoutesConfiguration(RoutingConfigurator $routes, string $confDir): void
    {
        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS);
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS);
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS);
    }

    /**
     * @return BundleInterface[]
     */
    private function registerBundlesFromFile(string $bundlesFile): iterable
    {
        $contents = require $bundlesFile;
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * @return string[]
     */
    private function getConfigurationDirectories(): iterable
    {
        yield $this->getProjectDir() . '/config';
    }
}
