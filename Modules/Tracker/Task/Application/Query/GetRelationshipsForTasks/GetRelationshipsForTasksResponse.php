<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetRelationshipsForTasks;

use Modules\Shared\Application\Query\QueryResponseInterface;

class GetRelationshipsForTasksResponse implements QueryResponseInterface
{
    /**
     * @param array<int, mixed> $relationships
     */
    public function __construct(
        private readonly array $relationships
    ) {
    }

    /**
     * @param array<int, mixed> $tasks
     */
    public static function fromArray(array $tasks): static
    {
        return new static($tasks);
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->relationships;
    }
}
