<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Illuminate\Support\Facades\DB;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

class UpdateTaskExecutorsService
{
    public function __construct(
        private readonly Task $task,
        private readonly TaskNotification $emailVerification
    )
    {
    }

    public static function make(Task $task, TaskNotification $emailVerification): static
    {
        return new static ($task, $emailVerification);
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
            $this->emailVerification->sendNotificationOfTaskAssignment($user, $this->task);
            $this->task->addExecutor($user);
        }
    }
}
