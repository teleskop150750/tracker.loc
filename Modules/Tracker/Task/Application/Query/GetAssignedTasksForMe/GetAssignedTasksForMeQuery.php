<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetAssignedTasksForMe;

use Modules\Shared\Application\Query\QueryInterface;

class GetAssignedTasksForMeQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
