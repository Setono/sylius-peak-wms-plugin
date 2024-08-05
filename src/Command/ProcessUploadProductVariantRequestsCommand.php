<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Command;

use Setono\SyliusPeakPlugin\Processor\UploadProductVariantRequestProcessorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'setono:sylius-peak-wms:process-upload-product-variant-requests',
    description: 'Processes upload product variant requests',
)]
final class ProcessUploadProductVariantRequestsCommand extends Command
{
    public function __construct(private readonly UploadProductVariantRequestProcessorInterface $uploadProductVariantRequestProcessor)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->uploadProductVariantRequestProcessor->process();

        return 0;
    }
}
