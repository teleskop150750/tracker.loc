<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Services;

use Modules\Auth\User\Domain\Entity\User;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;

class UpdateFolderSharedUsers
{
    private Folder $folder;
    private FolderRepositoryInterface $folderRepository;

    public function __construct(Folder $folder, FolderRepositoryInterface $folderRepository)
    {
        $this->folder = $folder;
        $this->folderRepository = $folderRepository;
    }

    public static function make(Folder $folder, FolderRepositoryInterface $folderRepository): static
    {
        return new static($folder, $folderRepository);
    }

    /**
     * @param User[] $users
     */
    public function updateSharedUsers(array $users): void
    {
        $inheritedUsersKeyById = [];
        $parents = $this->folderRepository->getParentFoldersEntity([$this->folder->getUuid()]);

        foreach ($parents as $parent) {
            $author = $parent->getAuthor();
            $inheritedUsersKeyById[$author->getUuid()->getId()] = $author;

            foreach ($parent->getSharedUsers() as $sharedUser) {
                $inheritedUsersKeyById[$sharedUser->getUuid()->getId()] = $sharedUser;
            }
        }

        $diffUsersKeyById = [];

        foreach ($users as $user) {
            if (isset($inheritedUsersKeyById[$user->getUuid()->getId()])) {
                break;
            }

            $diffUsersKeyById[$user->getUuid()->getId()] = $user;
        }

        /** @var Folder[] $children */
        $children = $this->folderRepository->children($this->folder, includeTasks: true, includeSharedUsers: true);

        foreach ($children as $child) {
            $sharedUsers = $child->getSharedUsers();

            foreach ($sharedUsers as $sharedUser) {
                if (isset($diffUsersKeyById[$sharedUser->getUuid()->getId()])) {
                    $sharedUser->removeSharedFolder($child);
                }
            }

            $tasks = $child->getTasks();
            foreach ($tasks as $task) {
                $executors = $task->getExecutors();

                foreach ($executors as $executor) {
                    if (isset($diffUsersKeyById[$executor->getUuid()->getId()])) {
                        $executor->removeAssignedTasks($task);
                    }
                }
            }
        }

        $currentSharedUsers = $this->folder->getSharedUsers();

        foreach ($currentSharedUsers as $currentSharedUser) {
            $currentSharedUser->removeSharedFolder($this->folder);
        }

        foreach ($diffUsersKeyById as $diffUser) {
            $diffUser->addSharedFolder($this->folder);
        }
    }

    private function getParent(): Folder
    {
        $parent = $this->folder->getParent();

        if (!$parent) {
            throw new \Exception('Родителя нет');
        }

        return $parent;
    }
}
