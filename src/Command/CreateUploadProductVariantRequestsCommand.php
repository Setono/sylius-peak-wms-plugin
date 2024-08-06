<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Command;

use Doctrine\Persistence\ManagerRegistry;
use DoctrineBatchUtils\BatchProcessing\SimpleBatchIteratorAggregate;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Factory\UploadProductVariantRequestFactoryInterface;
use Setono\SyliusPeakPlugin\Model\ProductVariantInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'setono:sylius-peak-wms:create-upload-product-variant-requests',
    description: 'Will create upload product variant requests for variants that are not uploaded yet',
)]
final class CreateUploadProductVariantRequestsCommand extends Command
{
    use ORMTrait;

    /**
     * @param class-string<ProductVariantInterface> $productVariantClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly UploadProductVariantRequestFactoryInterface $uploadProductVariantRequestFactory,
        private readonly string $productVariantClass,
    ) {
        parent::__construct();

        $this->managerRegistry = $managerRegistry;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $qb = $this
            ->getRepository($this->productVariantClass)
            ->createQueryBuilder('o')
            ->andWhere('o.peakUploadProductVariantRequest IS NULL')
        ;

        /** @var SimpleBatchIteratorAggregate<array-key, ProductVariantInterface> $iterator */
        $iterator = SimpleBatchIteratorAggregate::fromQuery($qb->getQuery(), 100);

        foreach ($iterator as $productVariant) {
            $productVariant->setPeakUploadProductVariantRequest($this->uploadProductVariantRequestFactory->createNew());
        }

        return 0;
    }
}
