<?php

declare(strict_types=1);

namespace Tests\Setono\SyliusPeakPlugin\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Setono\SyliusPeakPlugin\DependencyInjection\SetonoSyliusPeakWMSExtension;

/**
 * See examples of tests and configuration options here: https://github.com/SymfonyTest/SymfonyDependencyInjectionTest
 */
final class SetonoSyliusPeakWMSExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new SetonoSyliusPeakWMSExtension(),
        ];
    }

    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set(): void
    {
        $this->load();

        $this->assertContainerBuilderHasParameter('setono_sylius_peak_wms.api_key', '%env(PEAK_WMS_API_KEY)%');
    }
}
