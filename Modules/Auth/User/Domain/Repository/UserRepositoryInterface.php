<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    /**
     * @throws UserNotFoundException
     */
    public function find(UserUuid $id): User;

    public function findOrNull(UserUuid $uuid): ?User;

    public function findByEmailOrNull(UserEmail $email): ?User;

    /**
     * @return User[]
     */
    public function all(): array;

    /**
     * @param callable(QueryBuilder): QueryBuilder $filter
     *
     * @return array<int, mixed>
     */
    public function getUsersQuery(callable $filter): array;
//    ===============

    /**
     * @param array<string, mixed>       $criteria
     * @param null|array<string, string> $orderBy
     * @param null|int                   $limit
     * @param null|int                   $offset
     *
     * @return User[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, null|int $limit = null, null|int $offset = null): array;
}
