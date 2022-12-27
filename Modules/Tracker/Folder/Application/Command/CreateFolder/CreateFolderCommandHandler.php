<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\CreateFolder;

use App\Exceptions\HttpException;
use Doctrine\ORM\QueryBuilder;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserNotFoundException;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderName;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;

class CreateFolderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    /**
     * @throws HttpException
     */
    public function __invoke(CreateFolderCommand $command): void
    {
        try {
            $parentFolder = null;
            $folderType = $command->type ?: FolderType::DEFAULT;

            if ($command->parent) {
                $parentFolder = $this->getParentFolder($command);
            }

            $folder = new Folder(
                FolderUuid::fromNative($command->id),
                FolderName::fromNative($command->name),
                $this->getAuthor($command),
                FolderType::fromNative($folderType),
            );

            if ($parentFolder) {
                $folder->setParent($parentFolder);
            }

            foreach ($this->getSharedUsers($command->sharedUsers) as $sharedUser) {
                $folder->addSharedUser($sharedUser);
            }

            $this->folderRepository->save($folder);
        } catch (FolderNotFoundException $exception) {
            throw new HttpException('Родительская папка не найдена', 400, 400);
        } catch (UserNotFoundException $exception) {
            throw new HttpException('Пользователь не найдена', 400, 400);
        }
    }

    /**
     * @return array<string, mixed>
     *
     * @throws FolderNotFoundException
     */
    public function getParentFolder(CreateFolderCommand $command): Folder
    {
        $auth = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($auth, $command) {
            $qb->andWhere('f.id = :id')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('su.uuid', ':userId'),
                    $qb->expr()->eq('a.uuid', ':userId')
                ))
                ->setParameter('id', $command->parent)
                ->setParameter('userId', $auth->getUuid()->getId());

            return $qb;
        };

        return $this->folderRepository->getFolder($filter);
    }

    /**
     * @throws UserNotFoundException
     */
    private function getAuthor(CreateFolderCommand $command): User
    {
        if ($command->author) {
            return $this->userRepository->find(UserUuid::fromNative($command->author));
        }

        return $this->userFetcher->getAuthUser();
    }

    /**
     * @param array<int, string> $ids
     *
     * @return array<int, User>
     */
    private function getSharedUsers(array $ids): array
    {
        if (\count($ids) < 1) {
            return [];
        }

        return $this->userRepository->findBy(['uuid' => $ids]);
    }
}
