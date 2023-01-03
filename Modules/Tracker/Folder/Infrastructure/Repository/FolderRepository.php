<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Infrastructure\Repository;

use App\Support\Arr;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Shared\Infrastructure\Doctrine\AbstractDoctrineRepository;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\FolderClosure;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;

class FolderRepository extends AbstractDoctrineRepository implements FolderRepositoryInterface
{
    public function save(Folder $folder): void
    {
        $this->persistEntity($folder);
    }

    public function remove(Folder $folder): void
    {
        $this->removeEntity($folder);
    }

    /**
     * {@inheritdoc}
     */
    public function getFolder(callable $filter): Folder
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb->select('f', 'a', 'su')
            ->from(Folder::class, 'f')
            ->join('f.author', 'a')
            ->leftJoin('f.sharedUsers', 'su');

        $qb = $filter($qb);

        $response = $qb->getQuery()->getOneOrNullResult();

        if (!$response) {
            throw new FolderNotFoundException('Папка не найдена', 404, 404);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getFolderQuery(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb
            ->distinct()
            ->select(
                'f',
                'PARTIAL a.{uuid,createdAt,updatedAt,email.value,emailVerifiedAt.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
                'PARTIAL su.{uuid,createdAt,updatedAt,email.value,emailVerifiedAt.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
                'p.id as parentId'
            )
            ->from(Folder::class, 'f')
            ->join('f.author', 'a')
            ->leftJoin('f.parent', 'p')
            ->leftJoin('f.sharedUsers', 'su');

        $qb = $filter($qb);

        $response = $qb->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        if (!$response) {
            throw new FolderNotFoundException('Папка не найдена', 404, 404);
        }

        $response = ['parentId' => $response['parentId'], ...$response[0]];
        $response = $this->formatArray($response);

        return $this->formatArray($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getFolders(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb
            ->distinct()
            ->select('f')
            ->from(Folder::class, 'f')
            ->join('f.author', 'a')
            ->leftJoin('f.sharedUsers', 'su');

        $qb = $filter($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getFoldersQuery(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb
            ->distinct()
            ->select(
                'f',
                'PARTIAL a.{uuid,email.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
                'PARTIAL su.{uuid,email.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
                'PARTIAL p.{id}'
            )
            ->from(Folder::class, 'f')
            ->join('f.author', 'a')
            ->leftJoin('f.parent', 'p')
            ->leftJoin('f.sharedUsers', 'su');

        $qb = $filter($qb);

        $response = $qb->getQuery()->getArrayResult();

        return $this->formatArray($response);
    }

    /**
     * {@inheritdoc}
     */
    public function getFoldersUsers(callable $filter): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb
            ->select(
                'PARTIAL f.{id}',
                'PARTIAL a.{uuid,email.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
                'PARTIAL su.{uuid,email.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
            )
            ->from(Folder::class, 'f')
            ->join('f.author', 'a');

        $qb = $filter($qb);

        $response = $qb->getQuery()->getArrayResult();
        $users = [];

        foreach ($response as $item) {
            $new = [$item['author'], ...$item['sharedUsers']];
            $users = [...$users, ...$new];
        }

        $users = $this->formatArray($users);
        $users = Arr::values(Arr::keyBy($users, 'id'));

        return $this->formatArray($users);
    }

    /**
     * {@inheritdoc}
     */
    public function getClosestParentFolderQuery(callable $filter): ?string
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('PARTIAL c.{id}', 'PARTIAL node.{id}')
            ->from(FolderClosure::class, 'c')
            ->innerJoin('c.ancestor', 'node')
            ->setMaxResults(1)
            ->orderBy('node.level', 'DESC');

        $qb = $filter($qb);

        $response = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);

        if (!$response) {
            return null;
        }

        return $response['ancestor']['id'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableFoldersIds(User $user): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb
            ->distinct()
            ->select('PARTIAL f.{id}')
            ->from(Folder::class, 'f')
            ->join('f.author', 'a')
            ->leftJoin('f.sharedUsers', 'su')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('a.uuid', ':userId'),
                $qb->expr()->eq('su.uuid', ':userId'),
            ))
            ->setParameter('userId', $user->getUuid()->getId());

        $response = $qb->getQuery()->getArrayResult();

        $response = $this->formatArray($response);

        return Arr::pluck($response, 'id');
    }
//    ===========================
//    ===========================

    /**
     * @param FolderUuid[] $ids
     *
     * @return Folder[]
     */
    public function getParentFoldersEntity(array $ids = [], bool $include = false): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        $ids = Arr::map($ids, static fn (FolderUuid $id) => $id->getId());

        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('c', 'node', 'a', 'su')
            ->from(FolderClosure::class, 'c')
            ->innerJoin('c.ancestor', 'node')
            ->innerJoin('node.author', 'a')
            ->leftJoin('node.sharedUsers', 'su')
            ->orderBy('c.depth', 'DESC')
            ->where('c.descendant IN (:ids)')
            ->setParameter('ids', $ids);

        if (false === $include) {
            $qb->andWhere('node.id NOT IN (:ids)');
        }

        $result = $qb->getQuery()->getResult();

        return Arr::map($result, static function (AbstractClosure $closure) {
            return $closure->getAncestor();
        });
    }

    public function getRootWorkspaceFolderForUser(UserUuid $userId)
    {
        $em = $this->entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select(
            'f',
            'PARTIAL a.{uuid,createdAt,updatedAt,email.value,emailVerifiedAt.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
            'PARTIAL su.{uuid,createdAt,updatedAt,email.value,emailVerifiedAt.value,fullName.firstName,fullName.lastName,fullName.patronymic,avatar.value,phone.value,department.value,post.value}',
        )
            ->from(Folder::class, 'f')
            ->join('f.author', 'a')
            ->leftJoin('f.sharedUsers', 'su')
            ->where('a.uuid = :author_id')
            ->andWhere('f.type.value = :type')
            ->setParameter('type', FolderType::ROOT)
            ->setParameter('author_id', $userId->getId())
            ->getQuery()
            ->getOneOrNullResult();
    }
//
//    public function getFoldersFromWorkSpace(UserUuid $userId)
//    {
//        $em = $this->entityManager();
//        $qb = $em->createQueryBuilder();
//
//        return $qb
//            ->from(Folder::class, 'f')
//            ->select('f')
//            ->orderBy('f.createdAt', 'DESC')
//            ->getQuery()
//            ->getResult();
//    }

    public function children(
        Folder|null $node = null,
        bool $direct = false,
        string|array|null $sortByField = null,
        string|array $direction = 'ASC',
        bool $includeNode = false,
        bool $includeTasks = false,
        bool $includeSharedUsers = false,
    ): ?array {
        /** @var QueryBuilder $qb */
        $qb = $this->repository(Folder::class)
            ->getChildrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode);
        $qb
            ->select('c', 'node')
            ->orderBy('node.createdAt', 'DESC');

        if ($includeTasks) {
            $qb->addSelect('t', 'e')
                ->leftJoin('node.tasks', 't')
                ->leftJoin('t.executors', 'e')
                ->addOrderBy('t.endDate.value', 'DESC');
        }

        if ($includeSharedUsers) {
            $qb->addSelect('sh')
                ->leftJoin('node.sharedUsers', 'sh');
        }

        $result = $qb->getQuery()->getResult();

        if ($node) {
            $result = Arr::map($result, static function (AbstractClosure $closure) {
                return $closure->getDescendant();
            });
        }

        return $result;
    }

    public function childrenForArr(
        array $node = [],
        bool $direct = false,
        string|array|null $sortByField = null,
        string|array $direction = 'ASC',
        bool $includeNode = false
    ): ?array {
        /** @var QueryBuilder $qb */
        $qb = $this->childrenQueryBuilderForArray($node, $direct, $sortByField, $direction, $includeNode);
        $qb->select('c', 'node')
            ->addSelect('a', 'su')
            ->join('node.author', 'a')
            ->leftJoin('node.sharedUsers', 'su')
            ->orderBy('node.createdAt', 'DESC');
        $result = $qb->getQuery()->getResult();

        if ($node) {
            $result = Arr::map($result, static function (AbstractClosure $closure) {
                return $closure->getDescendant();
            });
        }

        return $result;
    }

    public function childrenQueryBuilderForArray(
        array $nodes = [],
        $direct = false,
        $sortByField = null,
        string|array $direction = 'ASC',
        $includeNode = false
    ) {
        $meta = $this->entityManager()->getClassMetadata(Folder::class);
        $qb = $this->getQueryBuilder();

        if ($nodes) {
            $where = 'c.ancestor IN (:nodes) ';

            $qb->select('c, node')
                ->from(FolderClosure::class, 'c')
                ->innerJoin('c.descendant', 'node')
                ->orderBy('node.createdAt', 'DESC');

            $qb->where($where);

            if ($includeNode) {
                $qb->orWhere('c.ancestor = :nodes AND c.descendant = :nodes');
            }
        }

        if ($sortByField) {
            if (\is_array($sortByField)) {
                foreach ($sortByField as $key => $field) {
                    $fieldDirection = strtolower(\is_array($direction) ? ($direction[$key] ?? 'asc') : $direction);
                    if ($meta->hasField($field) && \in_array($fieldDirection, ['asc', 'desc'], true)) {
                        $qb->addOrderBy('node.'.$field, $fieldDirection);
                    } else {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Invalid sort options specified: field - %s, direction - %s',
                                $field,
                                $fieldDirection
                            )
                        );
                    }
                }
            } else {
                if ($meta->hasField($sortByField) && \in_array(strtolower($direction), ['asc', 'desc'], true)) {
                    $qb->orderBy('node.'.$sortByField, $direction);
                } else {
                    throw new InvalidArgumentException(
                        sprintf('Invalid sort options specified: field - %s, direction - %s', $sortByField, $direction)
                    );
                }
            }
        }

        $qb->setParameter('nodes', $nodes);

        return $qb;
    }

//    ===========================================
//    ===========================================
//    ===========================================
//    ===========================================

    // Workspace
    public function getWorkspaceFoldersIdsForUser(UserUuid $userId, bool $published = null): array
    {
        $rootId = $this->getWorkspaceFolderRootId($userId);

        return $this->getChildrenFoldersIds([$rootId], $published);
    }

    public function getWorkspaceFoldersForUser(
        UserUuid $userId,
        bool $includeTasks = false,
        bool $published = null,
        string $search = '',
    ): array {
        $rootId = $this->getWorkspaceFolderRootId($userId);
        $folders = $this->getChildrenFolders([$rootId], $includeTasks, $published, $search);

        return FolderFormatter::makeFromArray($folders)
            ->flatTasksInFolders()
            ->listToTree()
            ->formatTree(['/'])
            ->treeToList()
            ->getFolders();
    }

    // FoldersShared
    public function getFoldersSharedForUser(
        UserUuid $userId,
        bool $includeTasks = false,
        bool $published = null,
        string $search = '',
    ): array {
        $rootId = $this->getWorkspaceFolderRootId($userId);
        $workspaceIds = $this->getWorkspaceFoldersIds($rootId);
        // shared
        $rootSharedFoldersIds = $this->getRootFoldersSharedWithUser($userId);

        $parentSharedFolders = $this->getParentFolders($rootSharedFoldersIds, false);
        $childrenSharedFolders = $this->getChildrenFolders($rootSharedFoldersIds, $includeTasks, $published, $search);

        $allShared = [...$parentSharedFolders, ...$childrenSharedFolders];
        $parentSharedFoldersIds = Arr::keys($parentSharedFolders);
        $childrenSharedFoldersIds = Arr::keys($childrenSharedFolders);
        $allSharedIds = Arr::unique([...$parentSharedFoldersIds, ...$childrenSharedFoldersIds]);
        // public
        $publicIds = $this->getPublicFolderRootIds(Arr::unique([...$allSharedIds, ...$workspaceIds]));
        $parentPublicFolders = $this->getParentFolders($publicIds, false);
        $childrenPublicFolders = $this->getChildrenFolders($publicIds, $includeTasks, $published, $search);

        $parentPublicFoldersIds = Arr::keys($parentPublicFolders);
        $allPublic = [...$parentPublicFolders, ...$childrenPublicFolders];

        $allFolders = [...$allShared, ...$allPublic];
        $allParentsIds = Arr::values(Arr::unique([...$parentSharedFoldersIds, ...$parentPublicFoldersIds]));

        foreach ($allFolders as $key => $folder) {
            if (\in_array($key, $allParentsIds, true)) {
                $folder['name'] = 'Shared';
                $allFolders[$key] = $folder;
            }
        }

        $allFolders = FolderFormatter::makeFromArray($allFolders)
            ->flatTasksInFolders()
            ->listToTree()
            ->formatTree()
            ->treeToList()
            ->getFolders();

        foreach ($allFolders as $key => $folder) {
            if (\in_array($folder['id'], $allParentsIds, true)) {
                unset($allFolders[$key]);
            }
        }
        $allFolders = Arr::values($allFolders);

        return FolderFormatter::makeFromArray($allFolders)
            ->listToTree()
            ->generatePath(['Shared'])
            ->treeToList()
            ->getFolders();
    }

    // Available
    public function getAvailableFoldersIdsForUser(UserUuid $userId, bool $published = null): array
    {
        $rootId = $this->getWorkspaceFolderRootId($userId);
        $workspaceIds = $this->getChildrenFoldersIds([$rootId], $published);

        // shared
        $rootSharedFoldersIds = $this->getRootFoldersSharedWithUser($userId);
        $sharedFoldersIds = $this->getChildrenFoldersIds($rootSharedFoldersIds, $published);

        // public
        $rootPublicIds = $this->getPublicFolderRootIds(Arr::unique([...$sharedFoldersIds, ...$workspaceIds]));
        $publicIds = $this->getChildrenFoldersIds($rootPublicIds, $published);

        return Arr::unique([...$workspaceIds, ...$sharedFoldersIds, ...$publicIds]);
    }

    // Shared
    public function getSharedFoldersIdsForUser(UserUuid $userId, bool $published = null): array
    {
        $rootId = $this->getWorkspaceFolderRootId($userId);
        $workspaceIds = $this->getChildrenFoldersIds([$rootId], $published);

        // shared
        $rootSharedFoldersIds = $this->getRootFoldersSharedWithUser($userId);
        $sharedFoldersIds = $this->getChildrenFoldersIds($rootSharedFoldersIds, $published);

        // public
        $rootPublicIds = $this->getPublicFolderRootIds(Arr::unique([...$sharedFoldersIds, ...$workspaceIds]));
        $publicIds = $this->getChildrenFoldersIds($rootPublicIds, $published);

        return Arr::unique([...$sharedFoldersIds, ...$publicIds]);
    }

    private function getFolderProcess(callable $filter): QueryBuilder
    {
    }

//    public function searchFolders(string $search = '', array $ids = []): array
//    {
//        if (0 === \count($ids)) {
//            return [];
//        }
//
//        $em = $this->entityManager();
//        $queryBuilder = $em->createQueryBuilder();
//
//        $searchIds = $queryBuilder->select('f.id')
//            ->from(Folder::class, 'f')
//            ->where('f.id IN (:ids)')
//            ->setParameter('ids', $ids)
//            ->AndWhere('f.name.value LIKE :search')
//            ->setParameter('search', '%'.$search.'%')
//            ->distinct()
//            ->getQuery()
//            ->getArrayResult();
//
//        return Arr::pluck($searchIds, 'id');
//    }

    // Workspace
    private function getWorkspaceFolderRootId(UserUuid $userId): string
    {
        $em = $this->entityManager();
        $queryBuilder = $em->createQueryBuilder();

        return $queryBuilder->select('f.id')
            ->from(Folder::class, 'f')
            ->join('f.author', 'a')
            ->where('a.uuid = :author_id')
            ->andWhere('f.type.value = :type')
            ->setParameter('type', FolderType::ROOT)
            ->setParameter('author_id', $userId->getId())
            ->getQuery()->getSingleResult(Query::HYDRATE_ARRAY)['id'];
    }

    private function getWorkspaceFoldersIds(string $rootId): array
    {
        $em = $this->entityManager();
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder = $queryBuilder
            ->distinct()
            ->select('c', 'node.id')
            ->from(FolderClosure::class, 'c')
            ->innerJoin('c.descendant', 'node')
            ->where('c.ancestor = :id')
            ->setParameter(':id', $rootId);

        $result = $queryBuilder->getQuery()->getArrayResult();

        return Arr::pluck($result, 'id');
    }
    // Workspace END

    // Shared
    private function getRootFoldersSharedWithUser(UserUuid $userId): array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb
            ->distinct()
            ->select('f.id')
            ->from(Folder::class, 'f')
            ->join('f.sharedUsers', 'su')
            ->where('su.uuid = :user')
            ->setParameter('user', $userId->getId());

        $result = $qb->getQuery()
            ->getArrayResult();

        return Arr::pluck($result, 'id');
    }

    private function getPublicFolderRootIds(array $excludeIds = []): array
    {
        $em = $this->entityManager();
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder = $queryBuilder
            ->select('f')
            ->from(Folder::class, 'f')
            ->join('f.author', 'a')
            ->andWhere('f.type.value = :type')
            ->setParameter('type', FolderType::PUBLIC_ROOT)
            ->orderBy('f.createdAt', 'DESC');

        if (0 !== \count($excludeIds)) {
            $queryBuilder
                ->andWhere('f.id NOT IN (:excludeIds)')
                ->setParameter('excludeIds', $excludeIds);
        }
        $result = $queryBuilder->getQuery()->getArrayResult();

        return Arr::pluck($result, 'id');
    }
    // Shared END

    // FoldersShared
    private function getParentFolders(array $ids = [], bool $include = false): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        $em = $this->entityManager();
        $queryBuilder = $em->createQueryBuilder();

        $queryBuilder
            ->distinct()
            ->select('c', 'node', 'p.id parentId', 'a', 'su')
            ->from(FolderClosure::class, 'c')
            ->innerJoin('c.ancestor', 'node')
            ->leftJoin('node.parent', 'p')
            ->innerJoin('node.author', 'a')
            ->leftJoin('node.sharedUsers', 'su')
            ->where('c.descendant IN (:ids)')
            ->setParameter('ids', $ids);

        if (false === $include) {
            $queryBuilder->andWhere('node.id NOT IN (:ids)');
        }

        $result = $queryBuilder->getQuery()->getArrayResult();

        return FolderFormatter::makeFromArray($result)->formatDQLFolders()->getFolders();
    }

    private function getChildrenFolders(
        array $ids = [],
        bool $includeTasks = true,
        bool $published = null,
        string $search = ''
    ): array {
        if (0 === \count($ids)) {
            return [];
        }

        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb
            ->distinct()
            ->select('c', 'node', 'p.id parentId', 'a', 'su')
            ->from(FolderClosure::class, 'c')
            ->innerJoin('c.descendant', 'node')
            ->where('c.ancestor IN (:ids)')
            ->leftJoin('node.parent', 'p')
            ->innerJoin('node.author', 'a')
            ->leftJoin('node.sharedUsers', 'su')
            ->setParameter('ids', $ids)
            ->orderBy('node.createdAt', 'DESC');

        if ($includeTasks) {
            $qb = null !== $published
                ? $qb->leftJoin('node.tasks', 't', Join::WITH, 't.published.value = :published')
                : $qb->leftJoin('node.tasks', 't');

            $qb->leftJoin('t.executors', 'e')
                ->leftJoin('t.author', 'ta')
                ->addSelect('t', 'ta', 'e')
                ->addOrderBy('t.endDate.value', 'DESC');
        }

        if ($search) {
            $qb->AndWhere('node.name.value LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if (null !== $published) {
            $qb = $qb->AndWhere('node.published.value = :published')
                ->setParameter('published', $published);
        }

        $result = $qb->getQuery()->getArrayResult();

        return FolderFormatter::makeFromArray($result)->formatDQLFolders()->getFolders();
    }

    private function getChildrenFoldersIds(array $ids = [], bool $published = null): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb
            ->distinct()
            ->select('c', 'node.id')
            ->from(FolderClosure::class, 'c')
            ->innerJoin('c.descendant', 'node')
            ->where('c.ancestor IN (:ids)')
            ->setParameter('ids', $ids);

        if (null !== $published) {
            $qb = $qb->AndWhere('node.published.value = :published')
                ->setParameter('published', $published);
        }

        $result = $qb->getQuery()->getArrayResult();

        return Arr::pluck($result, 'id');
    }
}
