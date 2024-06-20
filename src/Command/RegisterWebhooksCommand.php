<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Command;

use Setono\SyliusPeakPlugin\Message\Command\RegisterWebhooks;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'setono:sylius-peak-wms:register-webhooks',
    description: 'Register relevant webhooks with Peak WMS',
)]
final class RegisterWebhooksCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $commandBus)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->commandBus->dispatch(new RegisterWebhooks());

        return 0;
    }
}
