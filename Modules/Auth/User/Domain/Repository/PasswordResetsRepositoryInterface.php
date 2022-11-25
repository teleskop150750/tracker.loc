<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Repository;

use Modules\Auth\User\Domain\Entity\PasswordResets;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;

interface PasswordResetsRepositoryInterface
{
    public function save(PasswordResets $passwordResets): void;

    public function findByEmailOrNull(UserEmail $email): ?PasswordResets;

    public function remove(PasswordResets $passwordResets): void;
}
