<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\TaskAddFile;

use Illuminate\Http\UploadedFile;
use Modules\Shared\Application\Command\CommandInterface;

class TaskAddFileCommand implements CommandInterface
{
    public function __construct(
        public readonly string $taskId,
        public readonly UploadedFile $file,
        public readonly string $fileId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): static
    {
        return new static($data['taskId'], $data['file'], $data['fileId']);
    }
}
