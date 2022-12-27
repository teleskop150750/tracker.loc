<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTask;

use Modules\Shared\Application\Query\QueryInterface;

class GetTaskQuery implements QueryInterface
{
    public function __construct(readonly string $id)
    {
    }

    public static function createFromArray(array $data): static
    {
        return new static($data['id'] ?? '',);
    }
}
