<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetWorkspaceFoldersForMe;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;

class GetWorkspaceFoldersForMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetWorkspaceFoldersForMeQuery $command): GetWorkspaceFoldersForMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $folders = $this->folderRepository->getWorkspaceFoldersForUser($user->getUuid(), includeTasks: true, published: true);
        $folders = FolderFormatter::makeFromArray($folders)
//            ->listToTree()
            ->getFolders();

        return GetWorkspaceFoldersForMeResponse::fromArray($folders);
    }
}
