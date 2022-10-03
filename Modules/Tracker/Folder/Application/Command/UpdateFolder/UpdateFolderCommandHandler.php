<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\UpdateFolder;

use Doctrine\ORM\EntityManagerInterface;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderAccess;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderName;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderPublished;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderNotFoundException;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\UpdateFolderAccess;
use Modules\Tracker\Folder\Domain\Services\UpdateFolderPublished;
use Modules\Tracker\Folder\Domain\Services\UpdateFolderSharedUsers;
use Webmozart\Assert\InvalidArgumentException;

class UpdateFolderCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(UpdateFolderCommand $command): void
    {
        try {
            $folder = $this->folderRepository->find(FolderUuid::fromNative($command->id));

            if (null !== $command->name) {
                $folder->setName(FolderName::fromNative($command->name));
            }

            if (null !== $command->published) {
                UpdateFolderPublished::make($folder, $this->folderRepository)
                    ->updatePublished(FolderPublished::fromNative($command->published));
            }

            if (null !== $command->access) {
                UpdateFolderAccess::make($folder, $this->folderRepository)
                    ->updateAccess(FolderAccess::fromNative($command->access));
            }

            if (null !== $command->sharedUsers) {
                UpdateFolderSharedUsers::make($folder, $this->folderRepository)
                    ->updateSharedUsers($this->getSharedUsers($command->sharedUsers));
            }

            $this->entityManager->flush();
        } catch (FolderNotFoundException $exception) {
            throw new InvalidArgumentException('папка не найдена');
        }
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
}
