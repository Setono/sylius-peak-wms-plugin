<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Provider;

use Doctrine\Persistence\ManagerRegistry;
use DoctrineBatchUtils\BatchProcessing\SelectBatchIteratorAggregate;
use Psr\EventDispatcher\EventDispatcherInterface;
use Setono\Doctrine\ORMTrait;
use Setono\SyliusPeakPlugin\Event\PreQualifiedUploadOrderRequestsQueryBuilderCreatedEvent;
use Setono\SyliusPeakPlugin\Model\OrderInterface;
use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Sylius\Component\Core\OrderCheckoutStates;
use Sylius\Component\Core\OrderPaymentStates;

final class PreQualifiedUploadOrderRequestsProvider implements PreQualifiedUploadOrderRequestsProviderInterface
{
    use ORMTrait;

    /**
     * @param class-string<UploadOrderRequestInterface> $uploadOrderRequestClass
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $uploadOrderRequestClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @return \Generator<array-key, UploadOrderRequestInterface>
     */
    public function getUploadOrderRequests(): \Generator
    {
        $qb = $this->getRepository($this->uploadOrderRequestClass)->createQueryBuilder('uor')
            ->join('uor.order', 'o')
            ->andWhere('uor.state = :state')
            ->andWhere('o.state = :orderState')
            ->andWhere('o.checkoutState = :checkoutState')
            ->andWhere('o.paymentState IN (:paymentStates)')
            ->setParameter('state', UploadOrderRequestInterface::STATE_PENDING)
            ->setParameter('orderState', OrderInterface::STATE_NEW)
            ->setParameter('checkoutState', OrderCheckoutStates::STATE_COMPLETED)
            ->setParameter('paymentStates', [OrderPaymentStates::STATE_PAID, OrderPaymentStates::STATE_AUTHORIZED])
        ;

        $this->eventDispatcher->dispatch(new PreQualifiedUploadOrderRequestsQueryBuilderCreatedEvent($qb));

        /** @var SelectBatchIteratorAggregate<array-key, UploadOrderRequestInterface> $iterator */
        $iterator = SelectBatchIteratorAggregate::fromQuery($qb->getQuery(), 50);

        yield from $iterator;
    }
}
