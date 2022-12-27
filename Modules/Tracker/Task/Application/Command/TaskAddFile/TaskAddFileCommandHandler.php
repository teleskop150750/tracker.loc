<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\TaskAddFile;

use Illuminate\Support\Facades\Storage;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\File\File;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileOriginName;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FilePath;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class TaskAddFileCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly FileRepositoryInterface $fileRepository,
    ) {
    }

    public function __invoke(TaskAddFileCommand $command): void
    {
        $task = $this->taskRepository->getTaskQuery(TaskUuid::fromNative($command->taskId));
        $fileName = Storage::putFile($command->taskId, $command->file);
        $file = new File(
            FileUuid::fromNative($command->fileId),
            $task,
            FileOriginName::fromNative($command->file->getClientOriginalName()),
            FilePath::fromNative($fileName)
        );

        $this->fileRepository->save($file);
    }
}
