<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTask;

use App\Support\Arr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Repository\Exceptions\TaskNotFoundException;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetTaskQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskRelationshipRepositoryInterface $taskRelationshipRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    /**
     * @throws TaskNotFoundException
     */
    public function __invoke(GetTaskQuery $command): GetTaskResponse
    {
        $task = $this->getTask($command);
        $allRelations = $this->getAllRelations($command);
        $CAN_BEGIN_TASK = $this->canBeginTask($allRelations);
        $RIGHTS = [
            'CAN_BEGIN_TASK' => $CAN_BEGIN_TASK,
        ];

        $task['RIGHTS'] = $RIGHTS;

        return GetTaskResponse::fromArray($task);
    }

    /**
     * @return array<string, any>
     *
     * @throws TaskNotFoundException
     */
    private function getTask(GetTaskQuery $command): array
    {
        $tasksIds = $this->getAvailableTasksIds();
        $folderIds = $this->getAvailableFoldersIds();

        $filter = static function (QueryBuilder $qb) use ($command, $tasksIds, $folderIds): QueryBuilder {
            return $qb->andWhere('t.uuid = :id')
                ->addSelect(
                    'PARTIAL tr.{uuid}',
                    'PARTIAL r.{uuid,createdAt,updatedAt,startDate.value,endDate.value,status.value,importance.value,description.value}',
                    'PARTIAL tir.{uuid}',
                    'PARTIAL l.{uuid,createdAt,updatedAt,startDate.value,endDate.value,status.value,importance.value,description.value}',
                    'f',
                )
                ->leftJoin('t.taskRelationships', 'tr')
                ->leftJoin('tr.right', 'r', Join::WITH, $qb->expr()->in('r.uuid', ':tasksIds'))
                ->leftJoin('t.inverseTaskRelationships', 'tir')
                ->leftJoin('tir.left', 'l', Join::WITH, $qb->expr()->in('l.uuid', ':tasksIds'))
                ->leftJoin('t.folders', 'f', Join::WITH, $qb->expr()->in('f.id', ':folderIds'))
                ->setParameter('id', $command->id)
                ->setParameter('folderIds', $folderIds)
                ->setParameter('tasksIds', $tasksIds);
        };

        $response = $this->taskRepository->getTaskQuery($filter);

        if (!isset($response['taskRelationships'])) {
            $response['taskRelationships'] = [];
        }

        if (!isset($response['inverseTaskRelationships'])) {
            $response['inverseTaskRelationships'] = [];
        }

        return $response;
    }

    /**
     * @return string[]
     */
    private function getAvailableTasksIds(): array
    {
        $auth = $this->userFetcher->getAuthUser();

        return $this->taskRepository->getAvailableTasksIds($auth);
    }

    /**
     * @return string[]
     */
    private function getAvailableFoldersIds(): array
    {
        $auth = $this->userFetcher->getAuthUser();

        return $this->folderRepository->getAvailableFoldersIds($auth);
    }

    /**
     * @return array<int, mixed>
     */
    private function getAllRelations(GetTaskQuery $command): array
    {
        $filter = static function (QueryBuilder $qb) use ($command): QueryBuilder {
            return $qb->select('PARTIAL tr.{uuid}')
                ->addSelect('PARTIAL r.{uuid,status.value}')
                ->join('tr.left', 'l')
                ->join('tr.right', 'r')
                ->where('l.uuid = :id')
                ->setParameter('id', $command->id)
                ->orderBy('r.endDate.value', 'ASC');
        };

        $response = $this->taskRelationshipRepository->getTasksRelationshipsQuery($filter);

        return Arr::pluck($response, 'right');
    }

    /**
     * @param array<int, mixed> $relations
     */
    private function canBeginTask(array $relations): bool
    {
        foreach ($relations as $task) {
            if (!TaskStatus::fromNative($task['status'])->sameValueAs(TaskStatus::fromNative(TaskStatus::DONE))) {
                return false;
            }
        }

        return true;
    }
}
