<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\DeleteFolder;

use Illuminate\Support\Facades\Storage;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;

class DeleteFolderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly FileRepositoryInterface $fileRepository,
    ) {
    }

    public function __invoke(DeleteFolderCommand $command): void
    {
        try {
            $folder = $this->folderRepository->find(FolderUuid::fromNative($command->id));
            $files = $this->fileRepository->getFilesInFolders([$command->id]);

            foreach ($files as $file) {
                Storage::delete($file->getPath()->toNative());
            }

            $this->folderRepository->remove($folder);
        } catch (FolderNotFoundException $exception) {
            throw new \InvalidArgumentException('папка не найдена');
        }
    }
}
