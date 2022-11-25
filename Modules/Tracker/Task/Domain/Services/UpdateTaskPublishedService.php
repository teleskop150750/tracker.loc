<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderPublished;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskPublished;

class UpdateTaskPublishedService
{
    private Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public static function make(Task $task): static
    {
        return new static($task);
    }

    public function updatePublished(TaskPublished $taskPublished): void
    {
        $folderPublished = FolderPublished::fromNative(true);
        $parent = $this->task->getFolder();

        if (!$parent?->getPublished()->sameValueAs($folderPublished)) {
            return;
        }

        $this->task->setPublished($taskPublished);
    }
}
