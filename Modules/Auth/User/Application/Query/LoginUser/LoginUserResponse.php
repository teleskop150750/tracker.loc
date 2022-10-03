<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\LoginUser;

use Modules\Auth\User\Domain\Entity\User;
use Modules\Shared\Application\Query\QueryResponseInterface;

class LoginUserResponse implements QueryResponseInterface
{
    public function __construct(
        private readonly string $id,
        private readonly string $email,
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $patronymic,
        private readonly ?string $department,
        private readonly ?string $post,
        private readonly ?string $avatar,
    ) {
    }

    public static function fromUser(User $user): self
    {
        return new self(
            $user->getUuid()->getId(),
            $user->getEmail()->toNative(),
            $user->getFullName()->getFirstName(),
            $user->getFullName()->getLastName(),
            $user->getFullName()->getPatronymic(),
            $user->getDepartment()->toNative(),
            $user->getPost()->toNative(),
            $user->getAvatar()->toNative(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'patronymic' => $this->patronymic,
            'department' => $this->department,
            'post' => $this->post,
            'avatar' => $this->avatar,
        ];
    }
}
