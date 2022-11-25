<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Repository;

use Doctrine\ORM\Query;
use Modules\Shared\Infrastructure\Doctrine\AbstractDoctrineRepository;
use Modules\Tracker\Task\Domain\Entity\File\File;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;

class FileRepository extends AbstractDoctrineRepository implements FileRepositoryInterface
{
    public function save(File $file): void
    {
        $this->persistEntity($file);
    }

    /**
     * @throws FileNotFoundException
     */
    public function findOrNull(FileUuid $id): ?File
    {
        return $this->repository(File::class)->findOneBy(['uuid' => $id->getId()]);
    }

    public function findForDownloadOrNull(FileUuid $id): ?array
    {
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();

        $qb = $qb->select('f')
            ->from(File::class, 'f')
            ->where('f.uuid = :id')
            ->setParameter('id', $id->getId());

        $response = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);

        return [
            'uuid' => $response['uuid']->getId(),
            'path' => $response['path.value'],
            'createdAt' => $response['createdAt'],
            'originName' => $response['originName.value'],
        ];
    }

    public function remove(File $file): void
    {
        $this->removeEntity($file);
    }

    /**
     * @param string[] $folderIds
     *
     * @return array<int, File>
     */
    public function getFilesInFolders(array $folderIds = []): array
    {
        if (0 === \count($folderIds)) {
            return [];
        }
        $em = $this->entityManager();
        $qb = $em->createQueryBuilder();
        $qb = $qb->select('f')
            ->from(File::class, 'f')
            ->leftJoin('f.task', 't')
            ->leftJoin('t.folder', 'folder')
            ->distinct()
            ->AndWhere('folder.id IN (:ids)')
            ->setParameter('ids', $folderIds);

        return $qb->getQuery()->getResult();
    }
}
