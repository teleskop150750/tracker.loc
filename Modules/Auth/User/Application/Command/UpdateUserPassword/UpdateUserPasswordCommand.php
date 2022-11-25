<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\UpdateUserPassword;

use Modules\Shared\Application\Command\CommandInterface;

class UpdateUserPasswordCommand implements CommandInterface
{
    public function __construct(
        public readonly string $currentPassword,
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
            $data['currentPassword'],
            $data['password'],
        );
    }
}
