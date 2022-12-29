<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Repository;

use App\Support\Arr;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Shared\Infrastructure\Doctrine\AbstractDoctrineRepository;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;

class TaskRelationshipRepository extends AbstractDoctrineRepository implements TaskRelationshipRepositoryInterface
{
    public function save(TaskRelationship $taskRelationship): void
    {
        $this->persistEntity($taskRelationship);
    }

    public function remove(TaskRelationship $taskRelationship): void
    {
        $this->removeEntity($taskRelationship);
    }

    /**
     * @return TaskRelationship[]
     */
    public function all(): array
    {
        return $this->repository(TaskRelationship::class)->findAll();
    }

    /**
     * @param array<string, mixed>       $criteria
     * @param null|array<string, string> $orderBy
     * @param null|int                   $limit
     * @param null|int                   $offset
     *
     * @return User[]
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        null|int $limit = null,
        null|int $offset = null
    ): array {
        return $this->repository(User::class)->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksRelationships(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('tr')
            ->from(TaskRelationship::class, 'tr');

        $qb = $filter($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getTasksRelationshipsQuery(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('tr')
            ->from(TaskRelationship::class, 'tr');

        $qb = $filter($qb);

        $response = $qb->getQuery()->getArrayResult();

        return $this->formatArray($response);
    }

    /**
     * @return TaskRelationship[]
     */
    public function getExpiredTasksRelationships(): array
    {
        $toDay = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('tr', 'l', 'r')
            ->from(TaskRelationship::class, 'tr')
            ->join('tr.left', 'l')
            ->join('tr.right', 'r')
            ->where('r.published.value = :published')
            ->setParameter('published', true)
            ->andWhere('tr.type.value IN (:types)')
            ->setParameter('types', [TaskRelationshipType::END_START])
            ->andWhere('r.status.value IN (:statuses)')
            ->setParameter('statuses', [TaskStatus::NEW, TaskStatus::IN_WORK, TaskStatus::WAITING])
            ->andWhere('r.endDate.value < :today')
            ->setParameter(':today', $toDay)
            ->orderBy('r.endDate.value', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string[] $ids
     */
    public function getRelationshipsForTasks(array $ids = []): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('tr.type.value type', 'l.uuid leftId', 'r.uuid rightId')
            ->from(TaskRelationship::class, 'tr')
            ->join('tr.left', 'l')
            ->join('tr.right', 'r')
//            ->where('l.published.value = :published')
//            ->AndWhere('r.published.value = :published')
//            ->setParameter('published', true)
            ->where('l.uuid IN (:ids)')
            ->andWhere('r.uuid IN (:ids)')
            ->setParameter('ids', $ids);

        $relationships = $qb->getQuery()->getResult();

        return Arr::map($relationships, static function ($relationship) {
            $relationship['leftId'] = $relationship['leftId']->getId();
            $relationship['rightId'] = $relationship['rightId']->getId();

            return $relationship;
        });
    }
}
