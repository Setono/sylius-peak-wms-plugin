<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Doctrine\Persistence\ManagerRegistry;
use DoctrineBatchUtils\BatchProcessing\SelectBatchIteratorAggregate;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Event\PreQualifiedUploadOrderRequestsQueryBuilderCreatedEvent;
use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;

final class PreQualifiedUploadProductVariantRequestsProvider implements PreQualifiedUploadProductVariantRequestsProviderInterface
{
    use ORMTrait;

    /**
     * @param class-string<UploadProductVariantRequestInterface> $uploadProductVariantRequestClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $uploadProductVariantRequestClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return \Generator<array-key, UploadProductVariantRequestInterface>
     */
    public function getUploadProductVariantRequests(): \Generator
    {
        $qb = $this->getRepository($this->uploadProductVariantRequestClass)->createQueryBuilder('o')
            ->andWhere('o.state = :state')
            ->setParameter('state', UploadProductVariantRequestInterface::STATE_PENDING)
        ;

        $this->eventDispatcher->dispatch(new PreQualifiedUploadOrderRequestsQueryBuilderCreatedEvent($qb));

        /** @var SelectBatchIteratorAggregate<array-key, UploadProductVariantRequestInterface> $iterator */
        $iterator = SelectBatchIteratorAggregate::fromQuery($qb->getQuery(), 50);

        yield from $iterator;
    }
}
