<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Repository;

use Modules\Auth\User\Domain\Entity\User;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;

interface TaskRelationshipRepositoryInterface
{
    public function save(TaskRelationship $taskRelationship): void;

    public function remove(TaskRelationship $taskRelationship): void;

    /**
     * @return TaskRelationship[]
     */
    public function all(): array;

    /**
     * @param array<string, mixed>       $criteria
     * @param null|array<string, string> $orderBy
     * @param null|int                   $limit
     * @param null|int                   $offset
     *
     * @return User[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, null|int $limit = null, null|int $offset = null): array;

    /**
     * @return TaskRelationship[]
     */
    public function getExpiredTasksRelationships(): array;

    /**
     * @param string[] $ids
     */
    public function getRelationshipsForTasks(array $ids = []): array;
}
