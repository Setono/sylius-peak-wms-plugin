<?php

declare(strict_types=1);

namespace Setono\SyliusPeakWMSPlugin\Event;

use Doctrine\ORM\QueryBuilder;

/**
 * This event is fired when the query builder for pre-qualified upload order requests has been created.
 * Listen to this event if you want to filter on associated orders of the upload order requests.
 */
final class PreQualifiedUploadOrderRequestsQueryBuilderCreatedEvent
{
    public function __construct(public readonly QueryBuilder $queryBuilder)
    {
    }
}
