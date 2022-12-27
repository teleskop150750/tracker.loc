<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Auth\User\Domain\Entity\User;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

class UpdateTaskExecutorsService
{
    public function __construct(private readonly Task $task)
    {
    }

    public static function make(Task $task): static
    {
        return new static ($task);
    }

    /**
     * @param User[] $users
     */
    public function updateExecutors(array $users = []): void
    {
        $currentExecutors = $this->task->getExecutors();

        foreach ($currentExecutors as $executor) {
            $this->task->removeExecutor($executor);
        }

        foreach ($users as $user) {
            $this->task->addExecutor($user);
        }
    }
}
