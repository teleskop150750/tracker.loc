<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\SearchTasks;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class SearchTasksQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(SearchTasksQuery $command): SearchTasksResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $folderIds = $this->folderRepository->getAvailableFoldersIdsForUser(userId: $user->getUuid(), published: true);
        $tasks = $this->taskRepository->getTasksInFolders($folderIds, published: true, search: $command->search);

        return SearchTasksResponse::fromArray($tasks);
    }
}
