<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\CreateTask;

use Modules\Shared\Application\Command\CommandInterface;

class CreateTaskCommand implements CommandInterface
{
    /**
     * @param string[]                                                                  $executors
     * @param array<int, array{taskRelationshipId: string, type: string, task: string}> $relationships
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $folder,
        public readonly string $status,
        public readonly string $importance,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $description,
        public readonly array $executors = [],
        public readonly array $relationships = [],
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
            $data['folder'],
            $data['status'],
            $data['importance'],
            $data['startDate'],
            $data['endDate'],
            $data['description'] ?? '',
            $data['executors'] ?? [],
            $data['relationships'] ?? [],
        );
    }
}
