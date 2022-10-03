<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetSharedGanttTasksForMe;

use App\Support\Arr;
use Illuminate\Support\Str;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetSharedGanttTasksForMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetSharedGanttTasksForMeQuery $command): GetSharedGanttTasksForMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $folderIds = $this->folderRepository->getSharedFoldersIdsForUser(userId: $user->getUuid(), published: true);
//        dd($folderIds);
        $tasks = $this->taskRepository->getTasksInFolders($folderIds, published: true);

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

        return GetSharedGanttTasksForMeResponse::fromArray($tasks);
    }
}
