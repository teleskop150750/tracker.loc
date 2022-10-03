<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Services;

class UserFormatter
{
    public static function make(): static
    {
        return new static();
    }

    public function formatDQLUsers(array $users = []): array
    {
        $result = [];

        foreach ($users as $user) {
            $result[] = $this->formatDQLUser($user);
        }

        return $result;
    }

    public function formatDQLUser(array $user): array
    {
        return [
            'id' => $user['uuid']->getId(),
            'fullName' => [
                'firstName' => $user['fullName.firstName'],
                'lastName' => $user['fullName.lastName'],
                'patronymic' => $user['fullName.patronymic'],
            ],
            'avatar' => $user['avatar.value'],
            'email' => $user['email.value'],
        ];
    }
}
