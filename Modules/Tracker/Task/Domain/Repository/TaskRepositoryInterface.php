<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Repository\Exceptions\TaskNotFoundException;

interface TaskRepositoryInterface
{
    public function save(Task $task): void;

    public function remove(Task $task): void;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     *
     * @throws TaskNotFoundException
     */
    public function getTask(callable $filter): Task;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     *
     * @return array<string, mixed>
     *
     * @throws TaskNotFoundException
     */
    public function getTaskQuery(callable $filter): array;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     *
     * @return Task[]
     */
    public function getTasks(callable $filter): array;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     *
     * @return array<int, mixed>
     */
    public function getTasksQuery(callable $filter): array;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     *
     * @return array<int, mixed>
     */
    public function getTasksUsers(callable $filter): array;

    /**
     * @return string[]
     */
    public function getAvailableTasksIds(User $user): array;
//    ===============================
//    ===============================
//    ===============================

    public function findOrNull(TaskUuid $id): ?Task;

    /**
     * @param array<string, mixed>       $criteria
     * @param null|array<string, string> $orderBy
     * @param null|int                   $limit
     * @param null|int                   $offset
     *
     * @return Task[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, null|int $limit = null, null|int $offset = null): array;

    public function getTasksCreatedByUser(UserUuid $id): array;

    public function getAssignedTasksForUser(UserUuid $id): array;

    /**
     * @return array<string, mixed>
     */
    public function getTaskInfo(TaskUuid $id): array;

    /**
     * @param string[] $folderIds
     *
     * @return array<int, mixed>
     */
    public function getTasksInFolders(array $folderIds = [], bool $includeAuthor = true, bool $includeExecutors = true, ?bool $published = null, string $search = ''): array;

    /**
     * @param string[] $excludeIds
     *
     * @return Task[]
     */
    public function getExpiredTasks(array $excludeIds = []): array;

//    /**
//     * @param string[] $foldersIds
//     *
//     * @return array<int, mixed>
//     */
//    public function searchTasksInFolders(string $search = '', array $foldersIds = []): array;
}
