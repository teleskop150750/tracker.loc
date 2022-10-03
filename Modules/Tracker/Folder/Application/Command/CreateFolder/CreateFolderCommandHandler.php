<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\CreateFolder;

use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserNotFoundException;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderAccess;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderName;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Webmozart\Assert\InvalidArgumentException;

class CreateFolderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserFetcherInterface $userFetcher,
    ) {
    }

    public function __invoke(CreateFolderCommand $command): void
    {
        try {
            $parentFolder = null;
            $folderType = $command->type ?: FolderType::DEFAULT;

            if ($command->parent) {
                $parentFolder = $this->folderRepository->find(FolderUuid::fromNative($command->parent));
                $folderType = $this->getFolderType($command, $parentFolder);
            }

            $folder = new Folder(
                FolderUuid::fromNative($command->id),
                FolderName::fromNative($command->name),
                $this->getAuthor($command),
                FolderAccess::fromNative($command->access),
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
            throw new InvalidArgumentException('Родительская папка не найдена');
        } catch (UserNotFoundException $e) {
            throw new InvalidArgumentException('Пользователь не найдена');
        }
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
     * @return User[]
     */
    private function getSharedUsers(array $ids): array
    {
        if (\count($ids) < 1) {
            return [];
        }

        return $this->userRepository->findBy(['uuid' => $ids]);
    }

    private function getFolderType(CreateFolderCommand $command, Folder $parentFolder): string
    {
        if (FolderAccess::PUBLIC === $parentFolder->getAccess()->getType()) {
            return FolderType::DEFAULT;
        }

        if (FolderAccess::PUBLIC === $command->access) {
            return FolderType::PUBLIC_ROOT;
        }

        return FolderType::DEFAULT;
    }
}
