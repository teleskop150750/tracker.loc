<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

class NewExecutorEvent extends Event
{
    use SerializesModels;

    public User $user;
    public Task $task;

    public function __construct(User $user, Task $task)
    {
        $this->user = $user;
        $this->task = $task;
    }
}
