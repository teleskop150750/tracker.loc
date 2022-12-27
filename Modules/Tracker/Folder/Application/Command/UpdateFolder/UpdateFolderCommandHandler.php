<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\UpdateFolder;

use App\Exceptions\HttpException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderName;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\UpdateFolderSharedUsers;

class UpdateFolderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    /**
     * @throws FolderNotFoundException
     * @throws HttpException
     */
    public function __invoke(UpdateFolderCommand $command): void
    {
        $folder = $this->getFolder($command->id);

        $this->checkFolder($folder);
        $this->updateName($folder, $command);
        $this->updateSharedUsers($folder, $command);

        $this->entityManager->flush();
    }

    private function getFolder(string $id): Folder
    {
        $auth = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($auth, $id) {
            $qb->andWhere('f.id = :id')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('su.uuid', ':userId'),
                    $qb->expr()->eq('a.uuid', ':userId')
                ))
                ->setParameter('id', $id)
                ->setParameter('userId', $auth->getUuid()->getId());

            return $qb;
        };

        return $this->folderRepository->getFolder($filter);
    }

    /**
     * @throws HttpException
     */
    private function checkFolder(Folder $folder): void
    {
        if (FolderType::ROOT === $folder->getType()->toNative()) {
            throw new HttpException('Недостаточно прав', 403, 403);
        }
    }

    private function updateName(Folder $folder, UpdateFolderCommand $command): void
    {
        if (null !== $command->name) {
            $folder->setName(FolderName::fromNative($command->name));
        }
    }

    private function updateSharedUsers(Folder $folder, UpdateFolderCommand $command): void
    {
        if (null !== $command->sharedUsers) {
            $users = $this->getSharedUsers($command->sharedUsers);
            UpdateFolderSharedUsers::make($folder)->updateSharedUsers($users);
        }
    }

    /**
     * @param array<int, string> $ids
     *
     * @return User[]
     */
    private function getSharedUsers(array $ids): array
    {
        if (\count($ids) < 1) {
            return [];
        }

        return $this->userRepository->findBy(['uuid' => $ids]);
    }
}
