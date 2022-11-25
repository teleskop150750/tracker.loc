<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Repository;

use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Repository\Exceptions\TaskNotFoundException;

interface TaskRepositoryInterface
{
    public function save(Task $task): void;

    public function remove(Task $task): void;

    /**
     * @throws TaskNotFoundException
     */
    public function find(TaskUuid $id): Task;

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
