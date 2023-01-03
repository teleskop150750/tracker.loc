<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\GetUsers;

use Modules\Shared\Application\Query\QueryInterface;

class GetUsersQuery implements QueryInterface
{
    public function __construct(
        public readonly ?string $search = null
    ) {
    }

    public static function make(): static
    {
        return new static();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): self
    {
        return new self(trim($data['search']) ?: null);
    }
}
