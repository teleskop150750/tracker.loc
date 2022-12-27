<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\Security;

use Modules\Auth\User\Domain\Entity\User;

interface UserFetcherInterface
{
    public function getAuthUser(): User;

    public function getAuthUserOrNull(): ?User;
}
