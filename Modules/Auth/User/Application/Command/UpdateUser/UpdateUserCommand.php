<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\UpdateUser;

use Modules\Shared\Application\Command\CommandInterface;

class UpdateUserCommand implements CommandInterface
{
    public function __construct(
        public readonly string $lastName,
        public readonly string $firstName,
        public readonly string $patronymic,
        public readonly ?string $phone = null,
        public readonly ?string $department = null,
        public readonly ?string $post = null,
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
            $data['lastName'],
            $data['firstName'],
            $data['patronymic'] ?? '',
            $data['phone'] ?? null,
            $data['department'] ?? null,
            $data['post'] ?? null,
        );
    }
}
