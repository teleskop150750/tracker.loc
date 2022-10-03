<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetFolder;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;

class GetFolderQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
    ) {
    }

    public function __invoke(GetFolderQuery $command): GetFolderResponse
    {
        $folders = $this->folderRepository->findFolderInfo(FolderUuid::fromNative($command->id));

        return GetFolderResponse::fromArray($folders);
    }
}
