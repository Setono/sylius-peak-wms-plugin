<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Command;

use Setono\SyliusPeakWMSPlugin\Processor\UploadOrderRequestProcessorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'setono:sylius-peak-wms:upload-orders',
    description: 'Upload orders to Peak WMS',
)]
final class UploadOrdersCommand extends Command
{
    public function __construct(private readonly UploadOrderRequestProcessorInterface $orderUploader)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->orderUploader->process();

        return 0;
    }
}
