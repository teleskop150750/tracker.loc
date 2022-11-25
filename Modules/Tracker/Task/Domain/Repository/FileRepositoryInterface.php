<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Repository;

use Modules\Tracker\Task\Domain\Entity\File\File;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;

interface FileRepositoryInterface
{
    public function save(File $file): void;

    public function remove(File $file): void;

    public function findOrNull(FileUuid $id): ?File;

    public function findForDownloadOrNull(FileUuid $id): ?array;

    /**
     * @param string[] $folderIds
     *
     * @return array<int, File>
     */
    public function getFilesInFolders(array $folderIds = []): array;
}
