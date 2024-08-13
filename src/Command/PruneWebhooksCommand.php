<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Command;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'setono:sylius-peak-wms:prune-webhooks',
    description: 'Will remove webhooks older than the defined threshold',
)]
final class PruneWebhooksCommand extends Command
{
    use ORMTrait;

    public function __construct(
        ManagerRegistry $managerRegistry,
        /** @var class-string $webhookClass */
        private readonly string $webhookClass,
        private readonly string $threshold = '-30 days',
    ) {
        parent::__construct();

        $this->managerRegistry = $managerRegistry;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this
            ->getManager($this->webhookClass)
            ->createQueryBuilder()
            ->delete($this->webhookClass, 'o')
            ->andWhere('o.createdAt < :threshold')
            ->setParameter('threshold', new \DateTimeImmutable($this->threshold))
            ->getQuery()
            ->execute()
        ;

        return 0;
    }
}
