<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\GetUsers;

use App\Support\Arr;
use JetBrains\PhpStorm\ArrayShape;
use Modules\Auth\User\Domain\Entity\User;
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
     * @param User[] $users
     *
     * @return static
     */
    public static function fromUsers(array $users): self
    {
        $users = Arr::map($users, static fn ($user) => [
            'id' => $user->getUuid()->getId(),
            'fullName' => [
                'firstName' => $user->getFullName()->getFirstName(),
                'lastName' => $user->getFullName()->getLastName(),
                'patronymic' => $user->getFullName()->getPatronymic() ?: null,
            ],
            'avatar' => $user->getAvatar()->toNative(),
            'email' => $user->getEmail()->toNative(),
            'post' => $user->getPost()->toNative(),
            'department' => $user->getDepartment()->toNative(),
        ]);

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
