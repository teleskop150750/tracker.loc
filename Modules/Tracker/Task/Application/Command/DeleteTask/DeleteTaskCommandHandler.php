<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\DeleteTask;

use Illuminate\Support\Facades\Storage;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Repository\Exceptions\TaskNotFoundException;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class DeleteTaskCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function __invoke(DeleteTaskCommand $command): void
    {
        try {
            $task = $this->taskRepository->getTaskQuery(TaskUuid::fromNative($command->id));

            foreach ($task->getFiles() as $file) {
                Storage::delete($file->getPath()->toNative());
            }

            $this->taskRepository->remove($task);
        } catch (TaskNotFoundException $exception) {
            throw new \InvalidArgumentException('Задача не найдена');
        }
    }
}
