<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTasks;

use Modules\Shared\Application\Query\QueryInterface;

class GetTasksQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
