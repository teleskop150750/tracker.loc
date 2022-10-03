<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\ExtendTasks;

use Modules\Shared\Application\Command\CommandInterface;

class ExtendTasksCommand implements CommandInterface
{
    public function __construct()
    {
    }

    public static function make(): static
    {
        return new static();
    }
}
