<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetRelationshipsForTasks;

use Modules\Shared\Application\Query\QueryInterface;

class GetRelationshipsForTasksQuery implements QueryInterface
{
    public function __construct(
        readonly array $taskIds
    ) {
    }

    public static function createFromArray(array $data): static
    {
        return new static(
            $data['taskIds'] ?? [],
        );
    }
}
