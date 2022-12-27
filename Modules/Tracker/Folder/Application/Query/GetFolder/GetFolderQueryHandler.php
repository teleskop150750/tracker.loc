<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetFolder;

use Doctrine\ORM\QueryBuilder;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;

class GetFolderQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    /**
     * @throws FolderNotFoundException
     */
    public function __invoke(GetFolderQuery $command): GetFolderResponse
    {
        $folder = $this->getFolder($command);

        return GetFolderResponse::fromArray($folder);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws FolderNotFoundException
     */
    public function getFolder(GetFolderQuery $command): array
    {
        $auth = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($auth, $command) {
            $qb->andWhere('f.id = :id')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('su.uuid', ':userId'),
                    $qb->expr()->eq('a.uuid', ':userId')
                ))
                ->setParameter('id', $command->id)
                ->setParameter('userId', $auth->getUuid()->getId());

            return $qb;
        };

        return $this->folderRepository->getFolderQuery($filter);
    }
}
