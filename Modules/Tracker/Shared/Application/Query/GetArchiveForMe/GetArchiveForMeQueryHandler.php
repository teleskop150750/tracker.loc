<?php

declare(strict_types=1);

namespace Modules\Tracker\Shared\Application\Query\GetArchiveForMe;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetArchiveForMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetArchiveForMeQuery $command): GetArchiveForMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $folders = $this->folderRepository->getWorkspaceFoldersForUser($user->getUuid(), false, false);
        $foldersIds = $this->folderRepository->getWorkspaceFoldersIdsForUser($user->getUuid());
        $tasks = $this->taskRepository->getTasksInFolders($foldersIds, published: false);
        $items = [...$folders, ...$tasks];
        $items = FolderFormatter::makeFromArray($items)
            ->listToTree()
            ->formatTree()
            ->treeToList()
            ->getFolders();

        return GetArchiveForMeResponse::fromArray($items);
    }
}
