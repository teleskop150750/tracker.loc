<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Repository;

use Modules\Auth\User\Domain\Entity\PasswordResets;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Auth\User\Domain\Repository\PasswordResetsRepositoryInterface;
use Modules\Shared\Infrastructure\Doctrine\AbstractDoctrineRepository;

class PasswordResetsRepository extends AbstractDoctrineRepository implements PasswordResetsRepositoryInterface
{
    public function save(PasswordResets $passwordResets): void
    {
        $this->persistEntity($passwordResets);
    }

    public function findByEmailOrNull(UserEmail $email): ?PasswordResets
    {
        return $this->repository(PasswordResets::class)->findOneBy(['email.value' => $email->toNative()]);
    }

    public function remove(PasswordResets $passwordResets): void
    {
        $this->removeEntity($passwordResets);
    }
}
