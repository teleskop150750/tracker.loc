<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\UpdateFolder;

use Modules\Shared\Application\Command\CommandInterface;

class UpdateFolderCommand implements CommandInterface
{
    public function __construct(
        public readonly string $id,
        public readonly null|string $name = null,
        public readonly null|bool $published = null,
        public readonly null|string $access = null,
        public readonly array $sharedUsers = [],
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
            $data['published'] ?? null,
            $data['access'] ?? null,
            $data['sharedUsers'] ?? [],
        );
    }
}
