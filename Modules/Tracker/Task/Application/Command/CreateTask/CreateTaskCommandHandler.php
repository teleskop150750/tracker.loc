<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\CreateTask;

use App\Exceptions\HttpException;
use App\Support\Arr;
use Doctrine\ORM\QueryBuilder;
use Illuminate\Support\Facades\Event;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Events\NewExecutorEvent;
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

class CreateTaskCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskRelationshipRepositoryInterface $taskRelationshipRepository,
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(CreateTaskCommand $command): void
    {
        $dependsTasks = $this->getDependsTasks($command);
        $affectsTasks = $this->getAffectsTasks($command);
        $startDate = $this->getStartDate($command);
        $endDate = $this->getEndDate($command);
        $this->checkStartDate($startDate, $affectsTasks);
        $this->checkEndDate($endDate, $dependsTasks);
        $author = $this->getAuthor();
        $folders = $this->getFolders($command->folders, $author);

        $task = new Task(
            TaskUuid::fromNative($command->id),
            TaskName::fromNative($command->name),
            $author,
            $startDate,
            $endDate,
            TaskStatus::fromNative($command->status),
            TaskImportance::fromNative($command->importance),
        );

        $this->addFolder($task, $folders);
        $this->addDescription($task, $command->description);
        $this->addExecutors($task, $command->executors);

        $this->taskRepository->save($task);

        $this->addDependsTasks($task, $dependsTasks);
        $this->addAffectsTasks($task, $affectsTasks);
    }

    /**
     * @return Task[]
     */
    private function getDependsTasks(CreateTaskCommand $command): array
    {
        if (0 === \count($command->depends)) {
            return [];
        }

        $auth = $this->userFetcher->getAuthUser();
        $filter = static function (QueryBuilder $qb) use ($auth, $command): QueryBuilder {
            return $qb->andWhere('t.uuid IN(:ids)')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.uuid', ':userId'),
                    $qb->expr()->eq('e.uuid', ':userId')
                ))
                ->setParameter('ids', $command->depends)
                ->setParameter('userId', $auth->getUuid()->getId());
        };

        return $this->taskRepository->getTasks($filter);
    }

    /**
     * @return Task[]
     */
    private function getAffectsTasks(CreateTaskCommand $command): array
    {
        if (0 === \count($command->depends)) {
            return [];
        }

        $auth = $this->userFetcher->getAuthUser();
        $filter = static function (QueryBuilder $qb) use ($auth, $command): QueryBuilder {
            return $qb->andWhere('t.uuid IN(:ids)')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.uuid', ':userId'),
                    $qb->expr()->eq('e.uuid', ':userId')
                ))
                ->setParameter('ids', $command->affects)
                ->setParameter('userId', $auth->getUuid()->getId());
        };

        return $this->taskRepository->getTasks($filter);
    }

    /**
     * @throws \Exception
     */
    private function getStartDate(CreateTaskCommand $command): TaskStartDate
    {
        return TaskStartDate::fromNative(new \DateTimeImmutable($command->startDate));
    }

    /**
     * @throws \Exception
     */
    private function getEndDate(CreateTaskCommand $command): TaskEndDate
    {
        return TaskEndDate::fromNative(new \DateTimeImmutable($command->endDate));
    }

    /**
     * @param Task[] $affectsTasks
     */
    private function checkStartDate(TaskStartDate $startDate, array $affectsTasks): void
    {
        if (0 === \count($affectsTasks)) {
            return;
        }

        $currentDate = $startDate->getDateTime()->getTimestamp();
        $dates = Arr::map($affectsTasks, static function (Task $task) {
            return $task->getEndDate()->getDateTime()->getTimestamp();
        });

        if ($currentDate < max($dates)) {
            new HttpException('Дата начала', 422, 422, ['dataStart' => ['Дата начала']]);
        }
    }

    /**
     * @param Task[] $dependsTasks
     *
     * @throws \Exception
     */
    private function checkEndDate(TaskEndDate $endDate, array $dependsTasks): void
    {
        if (0 === \count($dependsTasks)) {
            return;
        }

        $currentDate = $endDate->getDateTime()->getTimestamp();
        $dates = Arr::map($dependsTasks, static function (Task $task) {
            return $task->getStartDate()->getDateTime()->getTimestamp();
        });

        if ($currentDate > min($dates)) {
            new HttpException('Дата конца', 422, 422, ['dataEnd' => ['Дата конца']]);
        }
    }

    private function getAuthor(): User
    {
        return $this->userFetcher->getAuthUser();
    }

    /**
     * @param string[] $ids
     *
     * @return Folder[]
     */
    private function getFolders(array $ids, User $author): array
    {
        if (\count($ids) < 1) {
            return [];
        }

        $filter = static function (QueryBuilder $qb) use ($author, $ids): QueryBuilder {
            return $qb->andWhere('f.id IN (:ids)')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.uuid', ':userId'),
                    $qb->expr()->eq('su.uuid', ':userId')
                ))
                ->setParameter('ids', $ids)
                ->setParameter('userId', $author->getUuid()->getId());
        };

        return $this->folderRepository->getFolders($filter);
    }

    /**
     * @param string[] $ids
     *
     * @return User[]
     */
    private function getExecutors(array $ids): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        return $this->userRepository->findBy(['uuid' => $ids]);
    }

    // Add

    /**
     * @param Folder[] $folders
     */
    private function addFolder(Task $task, array $folders): void
    {
        foreach ($folders as $folder) {
            $task->addFolder($folder);
        }
    }

    private function addDescription(Task $task, string $description): void
    {
        $task->setDescription(TaskDescription::fromNative($description));
    }

    /**
     * @param array<string> $executorsIds
     */
    private function addExecutors(Task $task, array $executorsIds): void
    {
        $executors = $this->getExecutors($executorsIds);

        foreach ($executors as $executor) {
            $task->addExecutor($executor);
            Event::dispatch(new NewExecutorEvent($executor, $task));
        }
    }

    /**
     * @param Task[] $dependsTasks
     */
    private function addDependsTasks(Task $task, array $dependsTasks): void
    {
        if (0 === \count($dependsTasks)) {
            return;
        }

        $relationships = Arr::map(
            $dependsTasks,
            static fn (Task $dependsTask) => new TaskRelationship(
                TaskRelationshipUuid::generateRandom(),
                $task,
                $dependsTask,
                TaskRelationshipType::fromNative(TaskRelationshipType::END_START),
            )
        );

        foreach ($relationships as $relationship) {
            $this->taskRelationshipRepository->save($relationship);
        }
    }

    /**
     * @param Task[] $affectsTasks
     */
    private function addAffectsTasks(Task $task, array $affectsTasks): void
    {
        if (0 === \count($affectsTasks)) {
            return;
        }

        $relationships = Arr::map(
            $affectsTasks,
            static fn (Task $affectsTask) => new TaskRelationship(
                TaskRelationshipUuid::generateRandom(),
                $affectsTask,
                $task,
                TaskRelationshipType::fromNative(TaskRelationshipType::END_START),
            )
        );

        foreach ($relationships as $relationship) {
            $this->taskRelationshipRepository->save($relationship);
        }
    }
}
