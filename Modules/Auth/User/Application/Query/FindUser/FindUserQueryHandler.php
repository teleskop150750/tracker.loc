<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\FindUser;

use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Query\QueryHandlerInterface;

class FindUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(FindUserQuery $command): ?FindUserResponse
    {
        $foundUser = $this->userRepository->findOrNull(UserUuid::fromNative($command->id));

        return $foundUser ? FindUserResponse::fromUser($foundUser) : null;
    }
}
