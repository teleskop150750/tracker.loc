<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\TaskRemoveFile;

use Illuminate\Support\Facades\Storage;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;

class TaskRemoveFileCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FileRepositoryInterface $fileRepository,
    ) {
    }

    public function __invoke(TaskRemoveFileCommand $command): void
    {
        $file = $this->fileRepository->findOrNull(FileUuid::fromNative($command->fileId));

        if (!$file) {
            throw new \RuntimeException('Файл не найден');
        }

        Storage::delete($file->getPath()->toNative());
        $this->fileRepository->remove($file);
    }
}
