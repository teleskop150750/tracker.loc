<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTasksUnassembled;

use App\Support\Arr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetTasksUnassembledQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetTasksUnassembledQuery $command): GetTasksUnassembledResponse
    {
        $tasks = $this->getTasks();
        $tasks = $this->tasksFormat($tasks);

        return GetTasksUnassembledResponse::fromArray($tasks);
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

    private function getTasksIds(): array
    {
        $disabledIds = $this->getFolderIds();
        $auth = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($auth, $disabledIds): QueryBuilder {
            return $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('e.uuid', ':userId'),
            ))
                ->join(
                    't.folders',
                    'f',
                    Join::WITH,
                    $qb->expr()->orX(
                        $qb->expr()->notIn('f.id', ':folderIds')
                    )
                )
                ->select('PARTIAL t.{uuid}')
                ->setParameter('userId', $auth->getUuid()->getId())
                ->setParameter('folderIds', $disabledIds);
        };

        $response = $this->taskRepository->getTasksQuery($filter);

        return Arr::pluck($response, 'id');
    }

    /**
     * @return array<int, string>
     */
    private function getFolderIds(): array
    {
        $auth = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($auth) {
            return $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.uuid', ':userId')
            ))
                ->select('PARTIAL f.{id}')
                ->setParameter('userId', $auth->getUuid()->getId());
        };

        $folders = $this->folderRepository->getFoldersQuery($filter);

        return Arr::map($folders, static fn ($el) => $el['id']);
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
