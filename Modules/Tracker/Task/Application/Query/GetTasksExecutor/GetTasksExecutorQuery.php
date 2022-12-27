<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTasksExecutor;

use Modules\Shared\Application\Query\QueryInterface;

class GetTasksExecutorQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
