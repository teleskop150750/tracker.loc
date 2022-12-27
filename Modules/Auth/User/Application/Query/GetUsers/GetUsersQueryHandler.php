<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\GetUsers;

use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Query\QueryHandlerInterface;

class GetUsersQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(GetUsersQuery $command): ?GetUsersQueryResponse
    {
        $users = $this->userRepository->all();

        return GetUsersQueryResponse::fromUsers($users);
    }
}
