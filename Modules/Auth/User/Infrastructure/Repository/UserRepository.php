<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Repository;

use App\Exceptions\HttpException;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserNotFoundException;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Infrastructure\Doctrine\AbstractDoctrineRepository;

class UserRepository extends AbstractDoctrineRepository implements UserRepositoryInterface
{
    public function save(User $user): void
    {
        $this->persistEntity($user);
    }

    /**
     * @throws UserNotFoundException
     */
    public function find(UserUuid $id): User
    {
        $user = $this->findOrNull($id);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден', 404, 404);
        }

        return $user;
    }

    public function findOrNull(UserUuid $uuid): ?User
    {
        return $this->repository(User::class)->findOneBy(['uuid' => $uuid->getId()]);
    }

    public function findByEmailOrNull(UserEmail $email): ?User
    {
        return $this->repository(User::class)->findOneBy(['email.value' => $email->toNative()]);
    }

    /**
     * @return User[]
     */
    public function all(): array
    {
        return $this->repository(User::class)->findAll();
    }

    /**
     * @param array<string, mixed>       $criteria
     * @param null|array<string, string> $orderBy
     * @param null|int                   $limit
     * @param null|int                   $offset
     *
     * @return User[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, null|int $limit = null, null|int $offset = null): array
    {
        return $this->repository(User::class)->findBy($criteria, $orderBy, $limit, $offset);
    }
}
