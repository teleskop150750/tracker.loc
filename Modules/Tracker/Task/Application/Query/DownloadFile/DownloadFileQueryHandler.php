<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\DownloadFile;

use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;
use Modules\Tracker\Task\Domain\Services\FileStorageServivce;

class DownloadFileQueryHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly FileStorageServivce $fileStorageServivce,
        private readonly FileRepositoryInterface $fileRepository,
    ) {
    }

    public function __invoke(DownloadFileQuery $command): DownloadFileResponse
    {
        $response = $this->fileStorageServivce->download($command->fileId);

        return DownloadFileResponse::fromResponse($response);
    }
}
