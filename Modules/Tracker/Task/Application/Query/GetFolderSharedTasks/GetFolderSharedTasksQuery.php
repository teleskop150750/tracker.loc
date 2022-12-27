<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetFolderSharedTasks;

use Modules\Shared\Application\Query\QueryInterface;

class GetFolderSharedTasksQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
