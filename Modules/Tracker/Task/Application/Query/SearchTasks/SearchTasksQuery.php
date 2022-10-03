<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\SearchTasks;

use Modules\Shared\Application\Query\QueryInterface;

class SearchTasksQuery implements QueryInterface
{
    public function __construct(
        readonly string $search
    ) {
    }

    public static function createFromArray(array $data): static
    {
        return new static(
            $data['search'] ?? '',
        );
    }
}
