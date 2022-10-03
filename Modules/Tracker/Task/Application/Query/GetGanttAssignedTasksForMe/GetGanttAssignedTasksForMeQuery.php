<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetGanttAssignedTasksForMe;

use Modules\Shared\Application\Query\QueryInterface;

class GetGanttAssignedTasksForMeQuery implements QueryInterface
{
    public function __construct()
    {
    }

    public static function make(): static
    {
        return new static();
    }
}
