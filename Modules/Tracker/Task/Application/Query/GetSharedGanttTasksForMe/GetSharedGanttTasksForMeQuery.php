<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetSharedGanttTasksForMe;

use Modules\Shared\Application\Query\QueryInterface;

class GetSharedGanttTasksForMeQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
