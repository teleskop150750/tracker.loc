<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetFolderSharedTasks;

use App\Support\Arr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetFolderSharedTasksQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetFolderSharedTasksQuery $command): GetFolderSharedTasksResponse
    {
        $folders = $this->getFolders();
        $calcFolders = $this->calcFolders($folders);
        $filterFolders = $this->filterFolders($calcFolders);
        $ids = $this->getTasksIds($filterFolders);
        $folders = $this->getTasks($ids, $filterFolders);
        $folders = $this->tasksFormat($folders);

        return GetFolderSharedTasksResponse::fromArray($folders);
    }

    private function getFolders(): array
    {
        $taskIds = $this->getAvailableTasksIds();
        $user = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($user, $taskIds): QueryBuilder {
            return $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.uuid', ':userId'),
                $qb->expr()->eq('su.uuid', ':userId')
            ))
                ->leftJoin('f.tasks', 't', Join::WITH, $qb->expr()->in('t.uuid', ':taskIds'))
                ->select(
                    'PARTIAL f.{id,name.value,type.value}',
                    'PARTIAL p.{id,name.value,type.value}'
                )
                ->addSelect('PARTIAL t.{uuid}')
                ->setParameter('taskIds', $taskIds)
                ->setParameter('userId', $user->getUuid()->getId());
        };

        return $this->folderRepository->getFoldersQuery($filter);
    }

    /**
     * @param array<int, mixed> $folders
     *
     * @return array<int, mixed>
     */
    private function calcFolders(array $folders): array
    {
        $calcFolders = FolderFormatter::makeFromArray($folders)->listToTree()->formatTree()->treeToList()->getFolders();
        $calcFoldersKeyed = Arr::keyBy($calcFolders, 'id');
        $orphanFolders = $this->getOrphanFolders($calcFolders);

        $allowedIds = array_diff(Arr::pluck($calcFolders, 'id'), Arr::pluck($orphanFolders, 'id'));

        foreach ($orphanFolders as $orphanFolder) {
            $orphanFolderId = $orphanFolder['id'];
            $calcFoldersKeyed[$orphanFolderId]['parentId'] = $this->getParentFolder($orphanFolderId, $allowedIds);
        }

        $treeList = FolderFormatter::makeFromArray($calcFoldersKeyed)->listToTree()->formatTree()->getFolders();

        $usersTreeList = [];
        $sharedTreeList = [];
        $roots = [];

        foreach ($treeList as $tree) {
            if (FolderType::ROOT === $tree['type']) {
                $children = $tree['children'];
                $tree['children'] = [];
                $tree['path'] = ['Корень'];
                $tree['parentId'] = 'ROOT';
                $roots[] = $tree;

                foreach ($children as $item) {
                    $usersTreeList[] = $item;
                }
            } else {
                $sharedTreeList[] = $tree;
            }
        }

        $sharedTreeList = FolderFormatter::makeFromArray($sharedTreeList)->formatTree(['Доступные'])->getFolders();

        $sharedTreeList = Arr::map($sharedTreeList, static function ($sharedTree) {
            $sharedTree['parentId'] = 'SHARED';

            return $sharedTree;
        });

        $sharedList = FolderFormatter::makeFromArray($sharedTreeList)->treeToList()->getFolders();
        $sharedList = Arr::map($sharedList, static function ($item) {
            $item['type'] = 'SHARED';

            return $item;
        });

        $usersTreeList = Arr::map($usersTreeList, static function ($usersTree) {
            $usersTree['parentId'] = 'USER';

            return $usersTree;
        });

        $usersList = FolderFormatter::makeFromArray($usersTreeList)->treeToList()->getFolders();
        $usersList = Arr::map($usersList, static function ($item) {
            $item['type'] = 'USER';

            return $item;
        });

        return [...$usersList, ...$sharedList, ...$roots];
    }

    /**
     * @param array<int, mixed> $folders
     *
     * @return array<int, mixed>
     */
    private function getOrphanFolders(array $folders): array
    {
        return Arr::where($folders, static function ($folder) {
            return 0 === \count($folder['path']) && FolderType::DEFAULT === $folder['type'];
        });
    }

    /**
     * @param string[] $allowedIds
     */
    private function getParentFolder(string $id, array $allowedIds): ?string
    {
        $filter = static function (QueryBuilder $qb) use ($id, $allowedIds): QueryBuilder {
            return $qb->where('c.descendant = :id')
                ->andWhere('node.id IN (:allowedIds)')
                ->setParameter('allowedIds', $allowedIds)
                ->setParameter('id', $id);
        };

        return $this->folderRepository->getClosestParentFolderQuery($filter);
    }

    /**
     * @return string[]
     */
    private function getAvailableTasksIds(): array
    {
        $auth = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($auth): QueryBuilder {
            return $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.uuid', ':userId'),
                $qb->expr()->eq('e.uuid', ':userId')
            ))
                ->select('PARTIAL t.{uuid}')
                ->setParameter('userId', $auth->getUuid()->getId());
        };

        $response = $this->taskRepository->getTasksQuery($filter);

        return Arr::pluck($response, 'id');
    }

    /**
     * @param array<int, mixed> $folders
     *
     * @return array<int, mixed>
     */
    private function filterFolders(array $folders): array
    {
        return Arr::where($folders, static function (array $folder) {
            return 'SHARED' === $folder['type'];
        });
    }

    /**
     * @param array<int, mixed> $folders
     *
     * @return string[]
     */
    private function getTasksIds(array $folders): array
    {
        $tasks = array_filter(Arr::pluck($folders, 'tasks'));

        return Arr::flatten($tasks);
    }

    /**
     * @param string[]          $taskIds
     * @param array<int, mixed> $folder
     *
     * @return array<int, mixed>
     */
    private function getTasks(array $taskIds, array $folder): array
    {
        $folderIds = Arr::pluck($folder, 'id');

        $filter = static function (QueryBuilder $qb) use ($taskIds, $folderIds): QueryBuilder {
            return $qb->where('t.uuid IN (:taskIds)')
                ->addSelect(
                    'PARTIAL f.{id, name.value, type.value, level}',
                    'PARTIAL tr.{uuid}',
                    'PARTIAL r.{uuid,createdAt,updatedAt,startDate.value,endDate.value,status.value,importance.value}',
                    'PARTIAL tir.{uuid}',
                    'PARTIAL l.{uuid,createdAt,updatedAt,startDate.value,endDate.value,status.value,importance.value}',
                )
                ->leftJoin(
                    't.folders',
                    'f',
                    Join::WITH,
                    $qb->expr()->orX(
                        $qb->expr()->in('f.id', ':folderIds')
                    )
                )
                ->leftJoin('t.taskRelationships', 'tr')
                ->leftJoin('tr.right', 'r', Join::WITH, $qb->expr()->in('r.uuid', ':taskIds'))
                ->leftJoin('t.inverseTaskRelationships', 'tir')
                ->leftJoin('tir.left', 'l', Join::WITH, $qb->expr()->in('l.uuid', ':taskIds'))
                ->setParameter('taskIds', $taskIds)
                ->setParameter('folderIds', $folderIds);
        };

        return $this->taskRepository->getTasksQuery($filter);
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
