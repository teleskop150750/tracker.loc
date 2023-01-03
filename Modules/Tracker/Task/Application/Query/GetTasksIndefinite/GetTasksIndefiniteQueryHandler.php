<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTasksIndefinite;

use App\Support\Arr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetTasksIndefiniteQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetTasksIndefiniteQuery $command): GetTasksIndefiniteResponse
    {
        $tasks = $this->getTasks();
        $tasks = $this->tasksFormat($tasks);

        return GetTasksIndefiniteResponse::fromArray($tasks);
    }

    /**
     * @return array<int, mixed>
     */
    private function getTasks(): array
    {
        $taskIds = $this->getTasksIds();

        $filter = static function (QueryBuilder $qb) use ($taskIds): QueryBuilder {
            return $qb->where('t.uuid IN (:taskIds)')
                ->addSelect(
//                    'PARTIAL f.{id, name.value, type.value, level}',
                    'PARTIAL tr.{uuid}',
                    'PARTIAL r.{uuid,createdAt,updatedAt,startDate.value,endDate.value,status.value,importance.value}',
                    'PARTIAL tir.{uuid}',
                    'PARTIAL l.{uuid,createdAt,updatedAt,startDate.value,endDate.value,status.value,importance.value}',
                )
                ->leftJoin('t.taskRelationships', 'tr')
                ->leftJoin('tr.right', 'r', Join::WITH, $qb->expr()->in('r.uuid', ':taskIds'))
                ->leftJoin('t.inverseTaskRelationships', 'tir')
                ->leftJoin('tir.left', 'l', Join::WITH, $qb->expr()->in('l.uuid', ':taskIds'))
                ->setParameter('taskIds', $taskIds);
        };

        $response = $this->taskRepository->getTasksQuery($filter);

        return Arr::map($response, static function (array $task) {
            $task['folders'] = [];

            return $task;
        });
    }

    /**
     * @return string[]
     */
    private function getTasksIds(): array
    {
        $foldersIds = $this->getAvailableFoldersIds();
        $auth = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($auth, $foldersIds): QueryBuilder {
            return $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.uuid', ':userId'),
                $qb->expr()->eq('e.uuid', ':userId'),
            ))
                ->leftJoin(
                    't.folders',
                    'f',
                    Join::WITH,
                    $qb->expr()->orX(
                        $qb->expr()->in('f.id', ':folderIds')
                    )
                )
                ->select('PARTIAL t.{uuid}')
                ->addSelect('PARTIAL f.{id}')
                ->setParameter('userId', $auth->getUuid()->getId())
                ->setParameter('folderIds', $foldersIds);
        };

        $response = $this->taskRepository->getTasksQuery($filter);
        $response = Arr::where($response, static function (array $task) use ($foldersIds) {
            $ids = Arr::pluck($task['folders'], 'id');

            return 0 === \count(array_intersect($ids, $foldersIds));
        });

        return Arr::unique(Arr::pluck($response, 'id'));
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableFoldersIds(): array
    {
        $auth = $this->userFetcher->getAuthUser();

        return $this->folderRepository->getAvailableFoldersIds($auth);
    }

    /**
     * @param array<int, mixed> $tasks
     *
     * @return array<int, mixed>
     */
    private function tasksFormat(array $tasks): array
    {
        return Arr::map($tasks, static function ($task) {
            if (!$task['folders']) {
                $task['folders'] = [
                    [
                        'id' => null,
                        'level' => 1,
                        'name' => 'Неопределенныe',
                        'type' => 'INDEFINITE',
                    ],
                ];
            }

            $task['folders'] = [$task['folders'][0]];

            return $task;
        });
    }
}
