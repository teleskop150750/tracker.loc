<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\Events;

use Illuminate\Support\Facades\Mail;

class NewExecutorHandler
{
    public function handle(NewExecutorEvent $event): void
    {
        $data = [
            'name' => $event->user->getFullName()->getFullName(),
            'task' => $event->task->getName()->toNative(),
        ];

        Mail::send('mail.new-executor', $data, static function ($message) use ($event): void {
            $message->to(
                $event->user->getEmail()->toNative(),
                $event->user->getFullName()->getFullName()
            )->subject('Новая задача');
        });
    }
}
