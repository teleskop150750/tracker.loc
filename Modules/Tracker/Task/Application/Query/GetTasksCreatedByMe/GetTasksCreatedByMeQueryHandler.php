<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTasksCreatedByMe;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetTasksCreatedByMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetTasksCreatedByMeQuery $command): GetTasksCreatedByMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $tasks = $this->taskRepository->getTasksCreatedByUser($user->getUuid());

        return GetTasksCreatedByMeResponse::fromArray($tasks);
    }
}
