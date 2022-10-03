<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\DeleteTask;

use Modules\Shared\Application\Command\CommandInterface;

class DeleteTaskCommand implements CommandInterface
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
