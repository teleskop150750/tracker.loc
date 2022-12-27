<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Illuminate\Support\Facades\Mail;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

class TaskNotification
{
    public function sendNotificationOfTaskAssignment(User $user, Task $task): void
    {
        $data = ['name' => $user->getFullName()->getFullName(), 'taskId' => $task->getUuid()->getId()];
        Mail::send('mail.new-executor', $data, static function ($message) use ($user): void {
            $message->to($user->getEmail()->toNative(), $user->getFullName()->getFullName())
                ->subject('Поставлена задача');
        });
    }
}
