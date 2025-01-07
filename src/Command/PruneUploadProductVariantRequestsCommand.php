<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Command;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'setono:sylius-peak-wms:prune-upload-product-variant-requests',
    description: 'Will remove upload product variant requests older than the defined threshold',
)]
final class PruneUploadProductVariantRequestsCommand extends Command
{
    use ORMTrait;

    public function __construct(
        ManagerRegistry $managerRegistry,
        /** @var class-string<UploadProductVariantRequestInterface> $uploadProductVariantRequestClass */
        private readonly string $uploadProductVariantRequestClass,
        private readonly string $threshold = '-7 days',
    ) {
        parent::__construct();

        $this->managerRegistry = $managerRegistry;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this
            ->getManager($this->uploadProductVariantRequestClass)
            ->createQueryBuilder()
            ->delete($this->uploadProductVariantRequestClass, 'o')
            ->andWhere('o.createdAt < :threshold')
            ->setParameter('threshold', new \DateTimeImmutable($this->threshold))
            ->getQuery()
            ->execute()
        ;

        return 0;
    }
}
