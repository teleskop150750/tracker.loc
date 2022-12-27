<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\UpdateFolder;

use Modules\Shared\Application\Command\CommandInterface;

class UpdateFolderCommand implements CommandInterface
{
    /**
     * @param array<int, string> $sharedUsers
     */
    public function __construct(
        public readonly string $id,
        public readonly null|string $name = null,
        public readonly null|array $sharedUsers = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): static
    {
        return new static(
            $data['id'],
            $data['name'] ?? null,
            $data['sharedUsers'] ?? null,
        );
    }
}
