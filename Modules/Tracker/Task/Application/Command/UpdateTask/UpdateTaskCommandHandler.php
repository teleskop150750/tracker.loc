<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\UpdateTask;

use App\Exceptions\HttpException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskDescription;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskEndDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskImportance;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskName;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Repository\Exceptions\TaskNotFoundException;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;
use Modules\Tracker\Task\Domain\Services\UpdateTaskEndDateService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskExecutorsService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskFoldersService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskRelationsService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskStartDateService;
use Modules\Tracker\Task\Domain\Services\UpdateTaskStatusService;

class UpdateTaskCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserFetcherInterface $userFetcher,
        private readonly UpdateTaskRelationsService $updateTaskRelationsService,
        private readonly UpdateTaskStartDateService $updateTaskStartDateService,
        private readonly UpdateTaskEndDateService $updateTaskEndDateService,
        private readonly UpdateTaskStatusService $updateTaskStatusService,
    ) {
    }

    /**
     * @throws HttpException
     */
    public function __invoke(UpdateTaskCommand $command): void
    {
        try {
            $task = $this->getTask($command->id);
            $this->updateRelations($task, $command);

            $this->updateName($task, $command);
            $this->updateDescription($task, $command);
            $this->updateImportance($task, $command);
            $this->updateFolders($task, $command);
            $this->updateExecutors($task, $command);
            $this->updateStartDate($task, $command);
            $this->updateEndDate($task, $command);
            $this->updateStatus($task, $command);

            $this->entityManager->flush();
        } catch (TaskNotFoundException) {
            throw new HttpException('Задача не найдена', 422, 422);
        }
    }

    public function updateRelations(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->depends) {
            $depends = $this->getRelationshipsTasks($command->depends);
            $this->updateTaskRelationsService->updateDepends($task, $depends);
        }

        if (null !== $command->affects) {
            $affects = $this->getRelationshipsTasks($command->affects);
            $this->updateTaskRelationsService->updateAffects($task, $affects);
        }
    }

    /**
     * @throws TaskNotFoundException
     */
    private function getTask(string $id): Task
    {
        $auth = $this->userFetcher->getAuthUser();
        $filter = static function (QueryBuilder $qb) use ($auth, $id): QueryBuilder {
            return $qb->andWhere('t.uuid = :id')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.uuid', ':userId'),
                    $qb->expr()->eq('e.uuid', ':userId')
                ))
                ->setParameter('id', $id)
                ->setParameter('userId', $auth->getUuid()->getId());
        };

        return $this->taskRepository->getTask($filter);
    }

    private function updateName(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->name) {
            $task->setName(TaskName::fromNative($command->name));
        }
    }

    private function updateDescription(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->description) {
            $task->setDescription(TaskDescription::fromNative($command->description));
        }
    }

    private function updateImportance(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->importance) {
            $task->setImportance(TaskImportance::fromNative($command->importance));
        }
    }

    private function updateFolders(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->folders) {
            $folders = $this->getFolders($command->folders);
            UpdateTaskFoldersService::make($task)->updateFolders($folders);
        }
    }

    private function updateExecutors(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->executors) {
            UpdateTaskExecutorsService::make($task)->updateExecutors($this->getExecutors($command->executors));
        }
    }

    private function updateStartDate(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->startDate) {
            $this->updateTaskStartDateService->updateStartDate(
                $task,
                TaskStartDate::fromFormat(\DateTimeInterface::ATOM, $command->startDate)
            );
        }
    }

    private function updateEndDate(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->endDate) {
            $this->updateTaskEndDateService->updateEndDate($task, TaskEndDate::fromFormat(\DateTimeInterface::ATOM, $command->endDate));
        }
    }

    private function updateStatus(Task $task, UpdateTaskCommand $command): void
    {
        if (null !== $command->status) {
            $this->updateTaskStatusService->updateStatus($task, TaskStatus::fromNative($command->status));
        }
    }

    /**
     * @param array<int, string> $ids
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
     * @param array<int, string> $ids
     *
     * @return Folder[]
     */
    private function getFolders(array $ids): array
    {
        if (\count($ids) < 1) {
            return [];
        }
        $auth = $this->userFetcher->getAuthUser();
        $filter = static function (QueryBuilder $qb) use ($auth, $ids): QueryBuilder {
            return $qb->andWhere('f.id IN (:ids)')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.uuid', ':userId'),
                    $qb->expr()->eq('su.uuid', ':userId')
                ))
                ->setParameter('ids', $ids)
                ->setParameter('userId', $auth->getUuid()->getId());
        };

        return $this->folderRepository->getFolders($filter);
    }

    /**
     * @param null|string[] $ids
     *
     * @return Task[]
     */
    private function getRelationshipsTasks(?array $ids = null): array
    {
        if (null === $ids) {
            return [];
        }

        if (0 === \count($ids)) {
            return [];
        }

        $auth = $this->userFetcher->getAuthUser();
        $filter = static function (QueryBuilder $qb) use ($auth, $ids): QueryBuilder {
            return $qb->andWhere('t.uuid IN(:ids)')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.uuid', ':userId'),
                    $qb->expr()->eq('e.uuid', ':userId')
                ))
                ->setParameter('ids', $ids)
                ->setParameter('userId', $auth->getUuid()->getId());
        };

        return $this->taskRepository->getTasks($filter);
    }
}
