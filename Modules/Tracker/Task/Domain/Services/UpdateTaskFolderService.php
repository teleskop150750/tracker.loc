<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

class UpdateTaskFolderService
{
    private Task $task;

    private FolderRepositoryInterface $folderRepository;

    public function __construct(Task $task, FolderRepositoryInterface $folderRepository)
    {
        $this->task = $task;
        $this->folderRepository = $folderRepository;
    }

    public static function make(Task $task, FolderRepositoryInterface $folderRepository): static
    {
        return new static($task, $folderRepository);
    }

    /**
     * @throws FolderNotFoundException
     */
    public function updateFolder(FolderUuid $id): void
    {
        $folder = $this->getFolder($id);

        if ($this->task->getFolder()?->isEqualTo($folder)) {
            return;
        }

        $this->task->setFolder($folder);
    }

    /**
     * @throws \Exception
     */
    private function getFolder(FolderUuid $id): Folder
    {
        $folder = $this->folderRepository->find($id);

        if (!$folder) {
            throw new \Exception($id->getId());
        }

        return $folder;
    }
}
