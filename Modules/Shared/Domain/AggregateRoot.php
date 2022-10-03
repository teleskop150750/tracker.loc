<?php

declare(strict_types=1);

namespace Modules\Shared\Domain;

use Modules\Shared\Domain\ValueObject\Identity\UUID;

class AggregateRoot
{
    protected UUID $uuid;

    public function getUuid(): UUID
    {
        return $this->uuid;
    }

    public function isEqualTo(self $aggregateRoot): bool
    {
        return $this->getUuid()->getId() === $aggregateRoot->getUuid()->getId();
    }
}
