<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\TaskRemoveFile;

use Modules\Shared\Application\Command\CommandInterface;

class TaskRemoveFileCommand implements CommandInterface
{
    public function __construct(
        public readonly string $fileId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): static
    {
        return new static($data['fileId']);
    }
}
