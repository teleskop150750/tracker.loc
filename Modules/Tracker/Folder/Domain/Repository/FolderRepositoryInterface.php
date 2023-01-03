<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Repository;

use DateTimeImmutable;
use Doctrine\ORM\QueryBuilder;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;

interface FolderRepositoryInterface
{
    public function save(Folder $folder): void;

    public function remove(Folder $folder): void;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     *
     * @throws FolderNotFoundException
     */
    public function getFolder(callable $filter): Folder;

    /**
     * @return array<string, mixed>
     *
     * @throws FolderNotFoundException
     */
    public function getFolderQuery(callable $filter): array;

    /**
     * @return Folder[]
     */
    public function getFolders(callable $filter): array;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     *
     * @return array<int, array{
     *     id: string,
     *     level: int,
     *     name: string,
     *     type: string,
     *     published: bool,
     *     createdAt: DateTimeImmutable,
     *     updatedAt: DateTimeImmutable,
     *  }>
     */
    public function getFoldersQuery(callable $filter): array;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     */
    public function getFoldersUsers(callable $filter): array;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     */
    public function getClosestParentFolderQuery(callable $filter): ?string;

    /**
     * @return string[]
     */
    public function getAvailableFoldersIds(User $user): array;
//    ===========================
//    ===========================

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
