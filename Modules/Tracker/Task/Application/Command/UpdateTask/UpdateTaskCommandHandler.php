<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\UpdateTask;

use App\Support\Arr;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\ValueObject\DateTime\DateTime;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskDescription;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskEndDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskImportance;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskName;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskPublished;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipUuid;
use Modules\Tracker\Task\Domain\Repository\Exceptions\TaskNotFoundException;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;
use Modules\Tracker\Task\Domain\Services\UpdateTaskEndDateService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskExecutorsService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskFolderService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskPublishedService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskRelationsService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskStartDateService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskStatusService;

class UpdateTaskCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskRelationshipRepositoryInterface $taskRelationshipRepository,
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UpdateTaskCommand $command): void
    {
        try {
            $task = $this->taskRepository->find(TaskUuid::fromNative($command->id));

            if (null !== $command->name) {
                $task->setName(TaskName::fromNative($command->name));
            }

            if (null !== $command->description) {
                $task->setDescription(TaskDescription::fromNative($command->description));
            }

            if (null !== $command->importance) {
                $task->setImportance(TaskImportance::fromNative($command->importance));
            }

            if (null !== $command->published) {
                UpdateTaskPublishedService::make($task)->updatePublished(
                    TaskPublished::fromNative($command->published)
                );
            }

            if (null !== $command->folder) {
                UpdateTaskFolderService::make($task, $this->folderRepository)->updateFolder(
                    FolderUuid::fromNative($command->folder)
                );
            }

            if (null !== $command->executors) {
                UpdateTaskExecutorsService::make($task)->updateExecutors($this->getExecutors($command->executors));
            }

            if (null !== $command->relationships) {
                $relationships = $this->getRelationships($task, $command->relationships);
                UpdateTaskRelationsService::make($task, $this->taskRelationshipRepository)->updateRelations(
                    $relationships
                );
            }

            if (null !== $command->startDate) {
                UpdateTaskStartDateService::make($task)->updateStartDate(
                    TaskStartDate::fromFormat(DateTime::FRONTEND_FORMAT, $command->startDate)
                );
//                $task->setStartDate(TaskStartDate::fromFormat(DateTime::FRONTEND_FORMAT, $command->startDate));
            }

            if (null !== $command->endDate) {
                UpdateTaskEndDateService::make($task)->updateEndDate(
                    TaskEndDate::fromFormat(DateTime::FRONTEND_FORMAT, $command->endDate)
                );
//                $task->setEndDate(TaskEndDate::fromFormat(DateTime::FRONTEND_FORMAT, $command->endDate));
            }

            if (null !== $command->status) {
                UpdateTaskStatusService::make($task)->updateStatus(TaskStatus::fromNative($command->status));
            }

            $this->entityManager->flush();
        } catch (TaskNotFoundException $exception) {
            throw new InvalidArgumentException('папка не найдена');
        }
    }

    /**
     * @return User[]
     */
    private function getExecutors(array $ids): array
    {
        if (\count($ids) < 1) {
            return [];
        }

        return $this->userRepository->findBy(['uuid' => $ids]);
    }

    /**
     * @param array{taskRelationshipId: string, task: string, type: string} $initRelationships
     *
     * @return TaskRelationship[]
     */
    private function getRelationships(Task $leftTask, array $initRelationships): array
    {
        if (0 === \count($initRelationships)) {
            return [];
        }

        $taskIds = Arr::pluck($initRelationships, 'task');

        $tasks = $this->taskRepository->findBy(['uuid' => $taskIds]);

        /** @var array<string, Task> $tasksById */
        $tasksById = [];

        foreach ($tasks as $task) {
            $id = $task->getUuid()->getId();
            $tasksById[$id] = $task;
        }

        return Arr::map($initRelationships, static function ($value) use ($leftTask, $tasksById) {
            return new TaskRelationship(
                TaskRelationshipUuid::fromNative($value['taskRelationshipId']),
                $leftTask,
                $tasksById[$value['task']],
                TaskRelationshipType::fromNative($value['type']),
            );
        });
    }
}
