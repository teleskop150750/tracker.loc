<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetFolderMeTasks;

use Modules\Shared\Application\Query\QueryResponseInterface;

class GetFolderMeTasksResponse implements QueryResponseInterface
{
    /**
     * @param array<int, mixed> $tasks
     */
    public function __construct(
        private readonly array $tasks
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
        return $this->tasks;
    }
}
