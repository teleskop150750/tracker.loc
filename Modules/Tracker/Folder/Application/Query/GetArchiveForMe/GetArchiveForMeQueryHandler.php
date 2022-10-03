<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetArchiveForMe;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;

class GetArchiveForMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetArchiveForMeQuery $command): GetArchiveForMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $folders = $this->folderRepository->getWorkspaceFoldersForUser($user->getUuid(), true, false);
        $folders = FolderFormatter::makeFromArray($folders)
            ->listToTree()
            ->formatTree()
            ->getFolders();

        return GetArchiveForMeResponse::fromArray($folders);
    }
}
