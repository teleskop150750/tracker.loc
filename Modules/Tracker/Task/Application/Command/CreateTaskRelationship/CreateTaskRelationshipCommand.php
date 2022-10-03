<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\CreateTaskRelationship;

use Modules\Shared\Application\Command\CommandInterface;

class CreateTaskRelationshipCommand implements CommandInterface
{
    public function __construct(
        public readonly string $taskId,
        public readonly array $relationships,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): static
    {
        return new static(
            $data['taskId'],
            $data['relationships'],
        );
    }
}
