<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\GetAllUsers;

use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Query\QueryHandlerInterface;

class GetALlUsersQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(GetALlUsersQuery $command): ?GetALlUsersQueryResponse
    {
        $users = $this->userRepository->all();

        return GetALlUsersQueryResponse::fromUsers($users);
    }
}
