<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetWorkspaceGanttTasksForMe;

use Modules\Shared\Application\Query\QueryInterface;

class GetWorkspaceGanttTasksForMeQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
