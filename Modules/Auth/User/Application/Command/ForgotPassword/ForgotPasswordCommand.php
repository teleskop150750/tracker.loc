<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\ForgotPassword;

use Modules\Shared\Application\Command\CommandInterface;

class ForgotPasswordCommand implements CommandInterface
{
    public function __construct(
        public readonly string $email,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return static
     */
    public static function createFromArray(array $data): self
    {
        return new self($data['email']);
    }
}
