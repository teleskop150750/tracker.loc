<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\FindUser;

use Modules\Shared\Application\Query\QueryInterface;

class FindUserQuery implements QueryInterface
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
        return new static($data['id']);
    }
}
