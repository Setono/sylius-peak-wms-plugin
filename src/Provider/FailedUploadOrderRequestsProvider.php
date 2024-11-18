<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Doctrine\Persistence\ManagerRegistry;
use DoctrineBatchUtils\BatchProcessing\SelectBatchIteratorAggregate;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Event\FailedUploadOrderRequestsQueryBuilderCreatedEvent;
use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;

final class FailedUploadOrderRequestsProvider implements FailedUploadOrderRequestsProviderInterface
{
    use ORMTrait;

    /**
     * @param class-string<UploadOrderRequestInterface> $uploadOrderRequestClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $uploadOrderRequestClass,
        private readonly string $processingTimeout = '1 hour',
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return \Generator<array-key, UploadOrderRequestInterface>
     */
    public function getUploadOrderRequests(): \Generator
    {
        $qb = $this->getRepository($this->uploadOrderRequestClass)->createQueryBuilder('o')
            ->andWhere('o.state = :state')
            ->andWhere('o.stateUpdatedAt < :stateUpdatedAt')
            ->setParameter('state', UploadOrderRequestInterface::STATE_PROCESSING)
            ->setParameter('stateUpdatedAt', new \DateTimeImmutable('-' . $this->processingTimeout))
        ;

        $this->eventDispatcher->dispatch(new FailedUploadOrderRequestsQueryBuilderCreatedEvent($qb));

        /** @var SelectBatchIteratorAggregate<array-key, UploadOrderRequestInterface> $iterator */
        $iterator = SelectBatchIteratorAggregate::fromQuery($qb->getQuery(), 50);

        yield from $iterator;
    }
}
