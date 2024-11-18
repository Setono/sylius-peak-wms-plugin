<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Event;

use Doctrine\ORM\QueryBuilder;

/**
 * This event is fired when the query builder for failed upload order requests has been created.
 * Listen to this event if you want to filter on associated orders of the upload order requests.
 */
final class FailedUploadOrderRequestsQueryBuilderCreatedEvent
{
    public function __construct(public readonly QueryBuilder $queryBuilder)
    {
    }
}
