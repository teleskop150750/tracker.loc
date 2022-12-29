<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetFolders;

use App\Support\Arr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetFoldersQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(GetFoldersQuery $command): GetFoldersResponse
    {
        $folders = $this->getFolders();
        $calcFolders = $this->calcFolders($folders);

        return GetFoldersResponse::fromArray($calcFolders);
    }

    private function getFolders(): array
    {
        $taskIds = $this->getTasksIds();
        $user = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($user, $taskIds): QueryBuilder {
            return $qb->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.uuid', ':userId'),
                $qb->expr()->eq('su.uuid', ':userId')
            ))
                ->leftJoin('f.tasks', 't', Join::WITH, $qb->expr()->in('t.uuid', ':taskIds'))
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

        $allowedIds = Arr::pluck($calcFolders, 'id');

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
                ->andWhere('node.id != :id')
                ->setParameter('allowedIds', $allowedIds)
                ->setParameter('id', $id);
        };

        return $this->folderRepository->getClosestParentFolderQuery($filter);
    }

    /**
     * @return string[]
     */
    private function getTasksIds(): array
    {
        $auth = $this->userFetcher->getAuthUser();

        return $this->taskRepository->getAvailableTasksIds($auth);
    }
}
