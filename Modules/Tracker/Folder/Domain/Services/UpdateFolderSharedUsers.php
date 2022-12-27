<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Services;

use Modules\Auth\User\Domain\Entity\User;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;

class UpdateFolderSharedUsers
{
    private readonly Folder $folder;

    public function __construct(Folder $folder)
    {
        $this->folder = $folder;
    }

    public static function make(Folder $folder): static
    {
        return new static($folder);
    }

    /**
     * @param User[] $users
     */
    public function updateSharedUsers(array $users): void
    {
        $currentSharedUsers = $this->folder->getSharedUsers();
        $currentSharedUsersKeyById = [];
        $usersKeyById = [];

        foreach ($currentSharedUsers as $user) {
            $currentSharedUsersKeyById[$user->getUuid()->getId()] = $user;
        }

        foreach ($users as $user) {
            $usersKeyById[$user->getUuid()->getId()] = $user;
        }

        $diffAdd = [];

        foreach ($users as $user) {
            if (isset($currentSharedUsersKeyById[$user->getUuid()->getId()])) {
                break;
            }

            $diffAdd[$user->getUuid()->getId()] = $user;
        }

        $diffDelete = [];

        foreach ($currentSharedUsers as $user) {
            if (isset($usersKeyById[$user->getUuid()->getId()])) {
                break;
            }

            $diffDelete[$user->getUuid()->getId()] = $user;
        }

        foreach ($diffDelete as $user) {
            $user->removeSharedFolder($this->folder);
        }

        foreach ($diffAdd as $user) {
            $user->addSharedFolder($this->folder);
        }
    }
}
