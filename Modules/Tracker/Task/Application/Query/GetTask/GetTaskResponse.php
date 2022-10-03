<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTask;

use Modules\Shared\Application\Query\QueryResponseInterface;

class GetTaskResponse implements QueryResponseInterface
{
    /**
     * @param array<int, mixed> $task
     */
    public function __construct(
        private readonly array $task
    ) {
    }

    /**
     * @param array<int, mixed> $task
     */
    public static function fromArray(array $task): static
    {
        return new static($task);
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->task;
    }
}
