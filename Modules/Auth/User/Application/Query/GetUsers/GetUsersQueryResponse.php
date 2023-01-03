<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\GetUsers;

use JetBrains\PhpStorm\ArrayShape;
use Modules\Shared\Application\Query\QueryResponseInterface;

class GetUsersQueryResponse implements QueryResponseInterface
{
    /**
     * @param array<int, mixed> $users
     */
    public function __construct(
        private readonly array $users
    ) {
    }

    /**
     * @param array<int, mixed> $users
     *
     * @return static
     */
    public static function fromArray(array $users): self
    {
        return new self($users);
    }

    #[ArrayShape(['meta' => 'array', 'data' => 'array'])]
    public function toArray(): array
    {
        return [
            'data' => $this->users,
            'meta' => [
                'total' => \count($this->users),
            ],
        ];
    }
}
