<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetFolderTasks;

use Modules\Shared\Application\Query\QueryInterface;

class GetFolderTasksQuery implements QueryInterface
{
    public function __construct(
        public readonly string $folderId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): static
    {
        return new static(
            $data['folderId'],
        );
    }
}
