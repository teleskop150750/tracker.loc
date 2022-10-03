<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\DeleteFolder;

use InvalidArgumentException;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;

class DeleteFolderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
    ) {
    }

    public function __invoke(DeleteFolderCommand $command): void
    {
        try {
            $folder = $this->folderRepository->find(FolderUuid::fromNative($command->id));
            $this->folderRepository->remove($folder);
        } catch (FolderNotFoundException $exception) {
            throw new InvalidArgumentException('папка не найдена');
        }
    }
}
