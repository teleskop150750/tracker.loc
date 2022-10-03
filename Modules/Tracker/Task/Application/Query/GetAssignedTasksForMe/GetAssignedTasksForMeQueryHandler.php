<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetAssignedTasksForMe;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetAssignedTasksForMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetAssignedTasksForMeQuery $command): GetAssignedTasksForMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $tasks = $this->taskRepository->getAssignedTasksForUser($user->getUuid());

        return GetAssignedTasksForMeResponse::fromArray($tasks);
    }
}
