<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\CreateTask;

use App\Support\Arr;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Shared\Domain\ValueObject\DateTime\DateTime;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskDescription;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskEndDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskImportance;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskName;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipUuid;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;
use Modules\Tracker\Task\Domain\Services\TaskNotification;

class CreateTaskCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskRelationshipRepositoryInterface $taskRelationshipRepository,
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserFetcherInterface $userFetcher,
        private readonly TaskNotification $emailVerification,
    ) {
    }

    public function __invoke(CreateTaskCommand $command): void
    {
        try {
            $folder = $this->folderRepository->find(FolderUuid::fromNative($command->folder));
            $startDateNative = \DateTimeImmutable::createFromFormat(DateTime::FRONTEND_FORMAT, $command->startDate)
                ->setTime(0, 0, 0, 0);
            $startDate = TaskStartDate::fromNative($startDateNative);
            $startEndNative = \DateTimeImmutable::createFromFormat(DateTime::FRONTEND_FORMAT, $command->endDate)
                ->setTime(0, 0, 0, 0);
            $endDate = TaskEndDate::fromNative($startEndNative);

            $task = new Task(
                TaskUuid::fromNative($command->id),
                TaskName::fromNative($command->name),
                $this->getAuthor(),
                $startDate,
                $endDate,
                $folder,
                TaskStatus::fromNative($command->status),
                TaskImportance::fromNative($command->importance),
            );

            if ($command->description) {
                $task->setDescription(TaskDescription::fromNative($command->description));
            }

            if (null !== $command->executors) {
                foreach ($this->getExecutors($command->executors) as $user) {
                    $task->addExecutor($user);
                    $this->emailVerification->sendNotificationOfTaskAssignment($user, $task);
                }
            }

            $this->taskRepository->save($task);

            if (null !== $command->relationships) {
                $relationships = $this->getRelationships($task, $command->relationships);

                foreach ($relationships as $relationship) {
                    $this->taskRelationshipRepository->save($relationship);
                }
            }
        } catch (FolderNotFoundException $exception) {
            throw new \InvalidArgumentException('Родительская папка не найдена');
        }
    }

    private function getAuthor(): User
    {
        return $this->userFetcher->getAuthUser();
    }

    /**
     * @param string[] $ids
     *
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
     * @param array<int, array{taskRelationshipId: string, task: string, type: string}> $initRelationships
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
