<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetSharedFoldersForMe;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;

class GetSharedFoldersForMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetSharedFoldersForMeQuery $command): GetSharedFoldersForMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $folders = $this->folderRepository->getFoldersSharedForUser(
            $user->getUuid(),
            includeTasks: true,
            published: true
        );

        $folders = FolderFormatter::makeFromArray($folders)
//            ->listToTree()
            ->getFolders();

        return GetSharedFoldersForMeResponse::fromArray($folders);
    }
}
