<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTasksUnassembled;

use Modules\Shared\Application\Query\QueryInterface;

class GetTasksUnassembledQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
