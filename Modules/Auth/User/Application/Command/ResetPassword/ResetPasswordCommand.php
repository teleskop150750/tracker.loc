<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\ResetPassword;

use Modules\Shared\Application\Command\CommandInterface;

class ResetPasswordCommand implements CommandInterface
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $token,
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
            $data['token'],
        );
    }
}
