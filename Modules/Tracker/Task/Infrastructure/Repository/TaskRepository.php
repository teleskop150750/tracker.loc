<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Repository;

use App\Support\Arr;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Shared\Infrastructure\Doctrine\AbstractDoctrineRepository;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Repository\Exceptions\TaskNotFoundException;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;
use Modules\Tracker\Task\Domain\Services\TaskFormatter;

class TaskRepository extends AbstractDoctrineRepository implements TaskRepositoryInterface
{
    public function save(Task $task): void
    {
        $this->persistEntity($task);
    }

    public function remove(Task $task): void
    {
        $this->removeEntity($task);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NonUniqueResultException
     */
    public function getTask(callable $filter): Task
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb->select('t', 'a', 'e', 'f')
            ->from(Task::class, 't')
            ->join('t.author', 'a')
            ->leftJoin('t.folders', 'f')
            ->leftJoin('t.executors', 'e');

        $qb = $filter($qb);

        $response = $qb->getQuery()->getOneOrNullResult();

        if (!$response) {
            throw new TaskNotFoundException('Задача не найдена', 404, 404);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaskQuery(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb->select(
            't',
            'PARTIAL a.{uuid,createdAt,updatedAt,email.value,emailVerifiedAt.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
            'PARTIAL e.{uuid,createdAt,updatedAt,email.value,emailVerifiedAt.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
        )
            ->from(Task::class, 't')
            ->join('t.author', 'a')
            ->leftJoin('t.executors', 'e');

        $qb = $filter($qb);

        $response = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (!$response) {
            throw new TaskNotFoundException('Задача не найдена', 404, 404);
        }

        return $this->formatArray($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasks(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('t')
            ->from(Task::class, 't')
            ->distinct()
            ->join('t.author', 'a')
            ->leftJoin('t.executors', 'e')
            ->orderBy('t.endDate.value', 'DESC');

        $qb = $filter($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksQuery(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select(
            'PARTIAL t.{uuid, createdAt, updatedAt, published.value, name.value, startDate.value, endDate.value, status.value, importance.value}',
            'PARTIAL a.{uuid, createdAt, updatedAt, email.value, emailVerifiedAt.value, fullName.firstName, fullName.lastName, fullName.patronymic, avatar.value, phone.value, department.value, post.value}',
            'PARTIAL e.{uuid, createdAt, updatedAt, email.value, emailVerifiedAt.value, fullName.firstName, fullName.lastName, fullName.patronymic, avatar.value, phone.value, department.value, post.value}',
        )
            ->from(Task::class, 't')
            ->join('t.author', 'a')
            ->leftJoin('t.executors', 'e')
            ->orderBy('t.endDate.value', 'DESC');

        $qb = $filter($qb);
        $response = $qb->getQuery()->getArrayResult();

        return $this->formatArray($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksUsers(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select(
            'PARTIAL t.{uuid}',
            'PARTIAL a.{uuid, createdAt, updatedAt, email.value, emailVerifiedAt.value, fullName.firstName, fullName.lastName, fullName.patronymic, avatar.value, phone.value, department.value, post.value}',
            'PARTIAL e.{uuid, createdAt, updatedAt, email.value, emailVerifiedAt.value, fullName.firstName, fullName.lastName, fullName.patronymic, avatar.value, phone.value, department.value, post.value}',
        )
            ->from(Task::class, 't')
            ->join('t.author', 'a');

        $qb = $filter($qb);
        $response = $qb->getQuery()->getArrayResult();
        $users = [];

        foreach ($response as $item) {
            $new = [$item['author'], ...$item['executors']];
            $users = [...$users, ...$new];
        }

        $users = $this->formatArray($users);
        $users = Arr::values(Arr::keyBy($users, 'id'));

        return $this->formatArray($users);
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableTasksIds(User $user): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb->select('PARTIAL t.{uuid}')
            ->distinct()
            ->from(Task::class, 't')
            ->join('t.author', 'a')
            ->leftJoin('t.executors', 'e')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.uuid', ':userId'),
                $qb->expr()->eq('e.uuid', ':userId')
            ))
            ->setParameter('userId', $user->getUuid()->getId());

        $response = $qb->getQuery()->getArrayResult();

        $response = $this->formatArray($response);

        return Arr::unique(Arr::pluck($response, 'id'));
    }

//    ======================================================
//    ======================================================
//    ======================================================
//    ======================================================

    public function findOrNull(TaskUuid $id): ?Task
    {
        return $this->repository(Task::class)->findOneBy(['uuid' => $id->getId()]);
    }

    /**
     * @param array<string, mixed>       $criteria
     * @param null|array<string, string> $orderBy
     * @param null|int                   $limit
     * @param null|int                   $offset
     *
     * @return Task[]
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        null|int $limit = null,
        null|int $offset = null
    ): array {
        return $this->repository(Task::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @return array<int, mixed>
     */
    public function getTasksCreatedByUser(UserUuid $id): array
    {
        $em = $this->entityManager();
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder = $queryBuilder->select('t', 'a', 'e')
            ->from(Task::class, 't')
            ->leftJoin('t.executors', 'e')
            ->leftJoin('t.author', 'a')
            ->where('a.uuid = :id')
            ->AndWhere('t.published.value = :published')
            ->orderBy('t.endDate.value', 'DESC')
            ->setParameter(':id', $id->getId())
            ->setParameter(':published', true)
            ->distinct();
        $result = $queryBuilder->getQuery()->getArrayResult();

        return TaskFormatter::make()->formatDqlTasks($result);
    }

    public function getAssignedTasksForUser(UserUuid $id): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb->select('t.uuid taskId')
            ->from(Task::class, 't')
            ->leftJoin('t.executors', 'e')
            ->where('e.uuid = :id')
            ->setParameter('id', $id->getId())
            ->AndWhere('t.published.value = :published')
            ->setParameter('published', true)
            ->distinct();
        $taskIds = $qb->getQuery()->getArrayResult();
        $taskIds = Arr::map($taskIds, static function ($taskId) {
            return $taskId['taskId']->getId();
        });

        $qb = $em->createQueryBuilder();
        $qb = $qb->select('t', 'a', 'e', 'f.name.value folderName', 't.uuid taskId')
            ->from(Task::class, 't')
            ->leftJoin('t.executors', 'e')
            ->leftJoin('t.author', 'a')
            ->leftJoin('t.folder', 'f')
            ->where('t.uuid IN (:ids)')
            ->setParameter('ids', $taskIds)
            ->orderBy('t.endDate.value', 'DESC')
            ->distinct();
        $result = $qb->getQuery()->getArrayResult();

        $tasks = Arr::pluck($result, 0);
        $tasks = TaskFormatter::make()->formatDqlTasks($tasks);
        $mapIds = [];

        foreach ($result as $item) {
            $mapIds[$item['taskId']->getId()] = $item['folderName'];
        }

        return Arr::map($tasks, static function ($task) use ($mapIds) {
            $task['path'][] = $mapIds[$task['id']];

            return $task;
        });
    }

//    public function getAssignedTasksIdsForUser(UserUuid $id): array
//    {
//        $em = $this->entityManager();
//        $queryBuilder = $em->createQueryBuilder();
//        $queryBuilder = $queryBuilder->select('t.uuid')
//            ->from(Task::class, 't')
//            ->leftJoin('t.executors', 'e')
//            ->where('e.uuid = :id')
//            ->setParameter('id', $id->getId())
//            ->AndWhere('t.published.value = :published')
//            ->setParameter('published', true)
//            ->distinct();
//        $ids = $queryBuilder->getQuery()->getArrayResult();
//
//        $result = [];
//
//        foreach ($ids as $el) {
//            $result[] = $el['uuid']->getId();
//        }
//
//        return $result;
//    }

//    /**
//     * @param string[] $foldersIds
//     *
//     * @return array<int, mixed>
//     */
//    public function searchTasksInFolders(string $search = '', array $foldersIds = []): array
//    {
//        if (0 === \count($foldersIds)) {
//            return [];
//        }
//
//        $em = $this->entityManager();
//        $qb = $em->createQueryBuilder();
//        $qb = $qb->select('t', 'a', 'e', 'f.id folderId', 'f.name.value folderName', 't.uuid taskId')
//            ->from(Task::class, 't')
//            ->leftJoin('t.author', 'a')
//            ->leftJoin('t.executors', 'e')
//            ->leftJoin('t.folder', 'f')
//            ->distinct()
//            ->where('t.published.value = :published')
//            ->setParameter('published', true)
//            ->AndWhere('f.id IN (:ids)')
//            ->setParameter('ids', $foldersIds)
//            ->AndWhere('t.name.value LIKE :name')
//            ->setParameter('name', '%'.$search.'%');
//
//        $result = $qb->getQuery()->getArrayResult();
//        $tasks = Arr::pluck($result, 0);
//        $tasks = TaskFormatter::make()->formatDqlTasks($tasks);
//        $mapIds = [];
//
//        foreach ($result as $item) {
//            $mapIds[$item['taskId']->getId()]['folderName'] = $item['folderName'];
//            $mapIds[$item['taskId']->getId()]['folderId'] = $item['folderId'];
//        }
//
//        return array_map(static function ($task) use ($mapIds) {
//            $task['path'][] = $mapIds[$task['id']]['folderName'];
//            $task['parentId'] = $mapIds[$task['id']]['folderId'];
//
//            return $task;
//        }, $tasks);
//    }

    /**
     * @param string[] $folderIds
     *
     * @return array<int, mixed>
     */
    public function getTasksInFolders(
        array $folderIds = [],
        bool $includeAuthor = true,
        bool $includeExecutors = true,
        ?bool $published = null,
        string $search = ''
    ): array {
        if (0 === \count($folderIds)) {
            return [];
        }
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('t', 'f.id folderId', 'f.name.value folderName', 't.uuid taskId')
            ->from(Task::class, 't')
            ->leftJoin('t.folder', 'f')
            ->distinct()
            ->AndWhere('f.id IN (:ids)')
            ->setParameter('ids', $folderIds)
            ->orderBy('t.endDate.value', 'DESC');

        if ($includeAuthor) {
            $qb = $qb->leftJoin('t.author', 'a')->addSelect('a');
        }

        if ($includeExecutors) {
            $qb = $qb->leftJoin('t.executors', 'e')->addSelect('e');
        }

        if (null !== $published) {
            $qb = $qb->AndWhere('t.published.value = :published')
                ->setParameter('published', $published);
        }

        if ('' !== $search) {
            $qb = $qb->AndWhere('t.name.value LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        $result = $qb->getQuery()->getArrayResult();

        $tasks = Arr::pluck($result, 0);
        $tasks = TaskFormatter::make()->formatDqlTasks($tasks);
        $mapIds = [];

        foreach ($result as $item) {
            $mapIds[$item['taskId']->getId()]['folderName'] = $item['folderName'];
            $mapIds[$item['taskId']->getId()]['folderId'] = $item['folderId'];
        }

        return Arr::map($tasks, static function ($task) use ($mapIds) {
            $task['path'][] = $mapIds[$task['id']]['folderName'];
            $task['parentId'] = $mapIds[$task['id']]['folderId'];

            return $task;
        });
    }

    public function getTaskInfo(TaskUuid $id): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('t', 'a', 'e', 'tr', 'r', 'ra', 're', 'fl', 'f.id folderId')
            ->from(Task::class, 't')
            ->leftJoin('t.author', 'a')
            ->leftJoin('t.executors', 'e')
            ->leftJoin('t.folder', 'f')
            ->leftJoin('t.files', 'fl')
            ->leftJoin('t.taskRelationships', 'tr')
            ->leftJoin('tr.right', 'r', Join::WITH, 'r.published.value = true')
            ->leftJoin('r.author', 'ra')
            ->leftJoin('r.executors', 're')
            ->AndWhere('t.uuid = :id')
            ->setParameter('id', $id->getId())
            ->orderBy('t.endDate.value', 'DESC');

        $taskResponse = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
        $task = $taskResponse[0];
        $description = $task['description.value'];
        $task = TaskFormatter::make()->formatDqlTask($task);
        $task['folderId'] = $taskResponse['folderId'];
        $task['description'] = $description;

        return $task;
    }

    /**
     * @param string[] $excludeIds
     *
     * @return Task[]
     */
    public function getExpiredTasks(array $excludeIds = []): array
    {
        $toDay = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('t')
            ->from(Task::class, 't')
            ->where('t.published.value = :published')
            ->setParameter('published', true)
            ->andWhere('t.status.value IN (:statuses)')
            ->setParameter('statuses', [TaskStatus::NEW, TaskStatus::IN_WORK, TaskStatus::WAITING])
            ->andWhere('t.endDate.value < :today')
            ->setParameter(':today', $toDay)
            ->orderBy('t.endDate.value', 'DESC');

        if (\count($excludeIds) > 0) {
            $qb->andWhere('t.uuid NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }

        return $qb->getQuery()->getResult();
    }
}
