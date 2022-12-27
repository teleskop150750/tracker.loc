<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

class UpdateTaskFoldersService
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

    /**
     * @param Folder[] $folders
     */
    public function updateFolders(array $folders = []): void
    {
        $currentFolders = $this->task->getFolders();

        foreach ($currentFolders as $folder) {
            $this->task->removeFolder($folder);
        }

        foreach ($folders as $folder) {
            $this->task->addFolder($folder);
        }
    }
}
