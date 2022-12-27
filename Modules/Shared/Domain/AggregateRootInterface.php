<?php

declare(strict_types=1);

namespace Modules\Shared\Domain;

interface AggregateRootInterface
{
    public function getUuid();

    public function isEqualTo(self $aggregateRoot): bool;
}
