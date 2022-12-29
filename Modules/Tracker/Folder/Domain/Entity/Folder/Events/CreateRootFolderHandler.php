<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Entity\Folder\Events;

use JetBrains\PhpStorm\NoReturn;
use Modules\Auth\User\Domain\Entity\Events\RegisterUserEvent;
use Modules\Tracker\Folder\Application\Command\CreateFolder\CreateFolderCommand;
use Modules\Tracker\Folder\Application\Command\CreateFolder\CreateFolderCommandHandler;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderName;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;

class CreateRootFolderHandler
{
    public function __construct(private readonly CreateFolderCommandHandler $createFolderCommandHandler)
    {
    }

    #[NoReturn]
    public function handle(RegisterUserEvent $event): void
    {
        $folderData = [
            'id' => FolderUuid::generateRandom()->getId(),
            'name' => FolderName::DEFAULT,
            'type' => FolderType::ROOT,
            'author' => $event->user->getUuid()->getId(),
        ];

        $this->createFolderCommandHandler->__invoke(CreateFolderCommand::createFromArray($folderData));
    }
}
