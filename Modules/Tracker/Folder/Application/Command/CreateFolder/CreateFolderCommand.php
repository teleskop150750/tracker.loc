<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Command\CreateFolder;

use Modules\Shared\Application\Command\CommandInterface;

class CreateFolderCommand implements CommandInterface
{
    /**
     * @param string[] $sharedUsers
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly array $sharedUsers = [],
        public readonly string|null $parent = null,
        public readonly string|null $type = null,
        public readonly string|null $author = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): static
    {
        return new static(
            $data['id'],
            $data['name'],
            $data['sharedUsers'] ?? [],
            $data['parent'] ?? null,
            $data['type'] ?? null,
            $data['author'] ?? null,
        );
    }
}
