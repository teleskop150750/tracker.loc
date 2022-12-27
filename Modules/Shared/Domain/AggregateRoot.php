<?php

declare(strict_types=1);

namespace Modules\Shared\Domain;

use Modules\Shared\Domain\ValueObject\Identity\UUIDInterface;

abstract class AggregateRoot implements AggregateRootInterface
{
    abstract public function getUuid(): UUIDInterface;

    public function isEqualTo(AggregateRootInterface $aggregateRoot): bool
    {
        return $this->getUuid()->getId() === $aggregateRoot->getUuid()->getId();
    }
}
