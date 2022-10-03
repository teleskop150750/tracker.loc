<?php

declare(strict_types=1);

namespace Modules\Shared\Domain;

interface AggregateRootInterface
{
    /**
     * @return mixed
     *
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getId();

    public function isEqualTo(self $aggregateRoot): bool;
}
