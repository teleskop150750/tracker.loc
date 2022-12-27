<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Security;

use Illuminate\Http\Request;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;

class UserFetcher implements UserFetcherInterface
{
    private ?User $user = null;

    public function __construct(
        private readonly Request $response,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @throws UnauthorizedException
     */
    public function getAuthUser(): User
    {
        try {
            if (null === $this->user) {
                $id = $this->response->header('user-id', '');
                $user = $this->userRepository->find(UserUuid::fromNative($id));

                $this->user = $user;
            }

            return $this->user;
        } catch (\Exception) {
            throw new UnauthorizedException('Ошибка авторизации', 401, 401);
        }
    }

    public function getAuthUserOrNull(): ?User
    {
        try {
            if (null === $this->user) {
                $id = $this->response->header('user-id', '');
                $user = $this->userRepository->findOrNull(UserUuid::fromNative($id));
                $this->user = $user;
            }

            return $this->user;
        } catch (\Exception) {
            return null;
        }
    }
}
