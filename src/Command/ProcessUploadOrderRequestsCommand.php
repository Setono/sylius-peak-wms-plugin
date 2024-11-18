<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Command;

use Setono\SyliusPeakPlugin\Processor\FailedUploadOrderRequestProcessorInterface;
use Setono\SyliusPeakPlugin\Processor\UploadOrderRequestProcessorInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'setono:sylius-peak-wms:process-upload-order-requests',
    description: 'Processes upload order requests',
)]
final class ProcessUploadOrderRequestsCommand extends Command
{
    public function __construct(
        private readonly UploadOrderRequestProcessorInterface $uploadOrderRequestProcessor,
        private readonly FailedUploadOrderRequestProcessorInterface $failedUploadOrderRequestProcessor,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->failedUploadOrderRequestProcessor->process();

        $this->uploadOrderRequestProcessor->process();

        return 0;
    }
}
