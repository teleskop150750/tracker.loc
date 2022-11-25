<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\DownloadFile;

use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;

class DownloadFileQueryHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FileRepositoryInterface $fileRepository,
    ) {
    }

    public function __invoke(DownloadFileQuery $command): DownloadFileResponse
    {
        $file = $this->fileRepository->findForDownloadOrNull(FileUuid::fromNative($command->fileId));

        if (!$file) {
            throw new \InvalidArgumentException('Файл не найден');
        }

        return DownloadFileResponse::fromArray($file);
    }
}
