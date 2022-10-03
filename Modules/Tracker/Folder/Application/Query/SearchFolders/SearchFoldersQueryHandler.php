<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\SearchFolders;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;

class SearchFoldersQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(SearchFoldersQuery $command): SearchFoldersQueryResponse
    {
        $user = $this->userFetcher->getAuthUser();
        $items = $this->folderRepository->getAvailableFoldersForUser(
            userId: $user->getUuid(),
            published: true,
            search: $command->search
        );

        return SearchFoldersQueryResponse::fromArray($items);
    }
}
