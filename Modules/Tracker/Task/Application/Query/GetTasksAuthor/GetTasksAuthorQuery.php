<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTasksAuthor;

use Modules\Shared\Application\Query\QueryInterface;

class GetTasksAuthorQuery implements QueryInterface
{
    public static function make(): static
    {
        return new static();
    }
}
