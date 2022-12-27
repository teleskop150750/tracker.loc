<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetFolderMeTasks;

use Modules\Shared\Application\Query\QueryInterface;

class GetFolderMeTasksQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
