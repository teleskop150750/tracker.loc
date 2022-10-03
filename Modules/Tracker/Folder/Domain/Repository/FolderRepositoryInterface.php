<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Repository;

use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;

interface FolderRepositoryInterface
{
    public function save(Folder $folder): void;

    public function remove(Folder $folder): void;

    /**
     * @throws FolderNotFoundException
     */
    public function find(FolderUuid $id): Folder;

    public function findOrNull(FolderUuid $id): ?Folder;

    /**
     * @return Folder[]
     */
    public function all(): array;

    public function findFolderInfo(FolderUuid $userId): array;

    /**
     * @param UserUuid[] $ids
     *
     * @return Folder[]
     */
    public function getParentFoldersEntity(array $ids = [], bool $include = false): array;

//    public function searchFolders(string $search = '', array $ids = []): array;

    public function getWorkspaceFoldersForUser(
        UserUuid $userId,
        bool $includeTasks = false,
        bool $published = null,
        string $search = ''
    ): array;

    /**
     * @return string[]
     */
    public function getWorkspaceFoldersIdsForUser(UserUuid $userId, bool $published = null): array;

    public function getFoldersSharedForUser(
        UserUuid $userId,
        bool $includeTasks = false,
        bool $published = null,
        string $search = ''
    ): array;

    public function getAvailableFoldersIdsForUser(UserUuid $userId, bool $published = null): array;

    public function getSharedFoldersIdsForUser(UserUuid $userId, bool $published = null): array;

    public function getAvailableFoldersForUser(
        UserUuid $userId,
        bool $includeTasks = false,
        bool $published = null,
        string $search = ''
    ): array;

    public function getRootWorkspaceFolderForUser(UserUuid $userId);

//    public function getFoldersFromWorkSpace(UserUuid $userId);

    public function children(
        Folder|null $node = null,
        bool $direct = false,
        string|array|null $sortByField = null,
        string|array $direction = 'ASC',
        bool $includeNode = false,
        bool $includeTasks = false,
        bool $includeSharedUsers = false,
    ): ?array;

    public function childrenForArr(
        array $node = [],
        bool $direct = false,
        string|array|null $sortByField = null,
        string|array $direction = 'ASC',
        bool $includeNode = false
    ): ?array;
}
