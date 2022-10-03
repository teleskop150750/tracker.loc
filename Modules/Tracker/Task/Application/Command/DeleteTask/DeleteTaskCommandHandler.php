<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\DeleteTask;

use InvalidArgumentException;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
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
            $folder = $this->taskRepository->find(TaskUuid::fromNative($command->id));
            $this->taskRepository->remove($folder);
        } catch (FolderNotFoundException $exception) {
            throw new InvalidArgumentException('Задача не найдена');
        }
    }
}
