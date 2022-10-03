<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\LoginUser;

use Modules\Shared\Application\Query\QueryInterface;

class LoginUserQuery implements QueryInterface
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['email'],
            $data['password'],
        );
    }
}
