<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetAvailableFoldersForMe;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;

class GetAvailableFoldersForMeQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetAvailableFoldersForMeQuery $command): GetAvailableFoldersForMeResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $folders = $this->folderRepository->getAvailableFoldersForUser($user->getUuid(), published: true);

        return GetAvailableFoldersForMeResponse::fromArray($folders);
    }
}
