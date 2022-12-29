<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\DeleteFolder;

use Doctrine\ORM\QueryBuilder;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;

class DeleteFolderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly FileRepositoryInterface $fileRepository,
    ) {
    }

    public function __invoke(DeleteFolderCommand $command): void
    {
        try {
            $folder = $this->getFolder($command);
//            $files = $this->fileRepository->getFilesInFolders([$command->id]);

//            foreach ($files as $file) {
//                Storage::delete($file->getPath()->toNative());
//            }

            $this->folderRepository->remove($folder);
        } catch (FolderNotFoundException $exception) {
        }
    }

    /**
     * @throws FolderNotFoundException
     */
    public function getFolder(DeleteFolderCommand $command): Folder
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

        return $this->folderRepository->getFolder($filter);
    }
}
