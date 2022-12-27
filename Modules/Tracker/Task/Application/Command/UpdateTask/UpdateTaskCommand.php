<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\UpdateTask;

use Modules\Shared\Application\Command\CommandInterface;

class UpdateTaskCommand implements CommandInterface
{
    /**
     * @param null|string[] $folders
     * @param null|string[] $executors
     * @param null|string[] $depends
     * @param null|string[] $affects
     */
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?array $folders = null,
        public readonly ?string $status = null,
        public readonly ?string $importance = null,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $description = null,
        public readonly ?array $executors = null,
        public readonly ?array $depends = null,
        public readonly ?array $affects = null,
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
            $data['folders'] ?? null,
            $data['status'] ?? null,
            $data['importance'] ?? null,
            $data['startDate'] ?? null,
            $data['endDate'] ?? null,
            $data['description'] ?? null,
            $data['executors'] ?? null,
            $data['depends'] ?? null,
            $data['affects'] ?? null,
        );
    }
}
