<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\UpdateTask;

use Modules\Shared\Application\Command\CommandInterface;

class UpdateTaskCommand implements CommandInterface
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $folder = null,
        public readonly ?string $status = null,
        public readonly ?string $importance = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $description = null,
        public readonly ?array $executors = null,
        public readonly ?array $relationships = null,
        public readonly ?bool $published = null,
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
            $data['folder'] ?? null,
            $data['status'] ?? null,
            $data['importance'] ?? null,
            $data['startDate'] ?? null,
            $data['endDate'] ?? null,
            $data['description'] ?? null,
            $data['executors'] ?? null,
            $data['relationships'] ?? null,
            $data['published'] ?? null,
        );
    }
}
