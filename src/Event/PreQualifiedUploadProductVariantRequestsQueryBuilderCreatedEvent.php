<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Event;

use Doctrine\ORM\QueryBuilder;

/**
 * This event is fired when the query builder for pre-qualified upload product variant requests has been created.
 * Listen to this event if you want to filter on associated variants or products of the upload product variant requests.
 */
final class PreQualifiedUploadProductVariantRequestsQueryBuilderCreatedEvent
{
    public function __construct(public readonly QueryBuilder $queryBuilder)
    {
    }
}
