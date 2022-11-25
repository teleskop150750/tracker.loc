<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Security;

use Illuminate\Http\Request;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserNotFoundException;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Webmozart\Assert\Assert;

class UserFetcher implements UserFetcherInterface
{
    private ?User $user = null;

    public function __construct(
        private readonly Request $response,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @throws UserNotFoundException
     */
    public function getAuthUser(): User
    {
        if (null === $this->user) {
            $id = $this->response->header('user-id');
            $user = $this->userRepository->find(UserUuid::fromNative($id));
            Assert::notNull($user, 'Current user not found check security access list');
            $this->user = $user;
        }

        return $this->user;
    }
}
