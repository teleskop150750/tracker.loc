<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\CreateTaskRelationship;

use Modules\Shared\Application\Command\CommandHandlerInterface;

class CreateTaskRelationshipCommandHandler implements CommandHandlerInterface
{
    public function __construct(
//        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function __invoke(
//        CreateTaskCommand $command
    ): void {
//        try {
//            $folder = $this->folderRepository->find(FolderUuid::fromNative($command->folder));
//            $startDate = TaskStartDate::fromFormat(TaskStartDate::FRONTEND_FORMAT, $command->startDate);
//            $endDate = TaskEndDate::fromFormat(TaskEndDate::FRONTEND_FORMAT, $command->endDate);
//
//            $task = new Task(
//                TaskUuid::fromNative($command->id),
//                TaskName::fromNative($command->name),
//                $this->getAuthor(),
//                $startDate,
//                $endDate,
//                $folder,
//                TaskStatus::fromNative($command->status),
//                TaskImportance::fromNative($command->importance),
//            );
//
//            if ($command->description) {
//                $task->setDescription(TaskDescription::fromNative($command->description));
//            }
//
//            foreach ($this->getExecutors($command->executors) as $user) {
//                $task->addExecutor($user);
//            }

//            $this->taskRepository->save($task);
//        } catch (FolderNotFoundException $exception) {
//            throw new InvalidArgumentException('Родительская папка не найдена');
//        }
    }
}
