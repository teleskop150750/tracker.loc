<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\DeleteFolder;

use Modules\Shared\Application\Command\CommandInterface;

class DeleteFolderCommand implements CommandInterface
{
    public function __construct(
        public readonly string $id,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): static
    {
        return new static(
            $data['id'],
        );
    }
}
