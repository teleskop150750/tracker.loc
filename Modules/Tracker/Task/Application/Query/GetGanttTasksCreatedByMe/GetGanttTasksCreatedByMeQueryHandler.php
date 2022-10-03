<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetGanttTasksCreatedByMe;

use App\Support\Arr;
use Illuminate\Support\Str;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetGanttTasksCreatedByMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetGanttTasksCreatedByMeQuery $command): GetGanttTasksCreatedByMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $tasks = $this->taskRepository->getTasksCreatedByUser($user->getUuid());

        $tasks = Arr::map($tasks, static function ($task) {
            $task['parent'] = 0;
            $task['text'] = $task['name'];
            $task['start_date'] = $task['startDate'];
            $task['progress'] = 0;
            $task['authorLabel'] = Str::substr(
                $task['author']['fullName']['firstName'],
                0,
                1
            ).' '.$task['author']['fullName']['lastName'];
            unset($task['parentId']);

            $executorsLabelList = Arr::map($task['executors'], static function ($user) {
                return Str::substr(
                    $user['fullName']['firstName'],
                    0,
                    1
                ).' '.$user['fullName']['lastName'];
            });
            $task['executorsLabel'] = Arr::join($executorsLabelList, ' | ');

            return $task;
        });

        return GetGanttTasksCreatedByMeResponse::fromArray($tasks);
    }
}
