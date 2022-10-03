<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\LoginUser;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Application\Query\FindUser\FindUserResponse;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Query\QueryHandlerInterface;

class LoginUserQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(LoginUserQuery $command): FindUserResponse
    {
        $foundUserByEmail = $this->userRepository->findByEmailOrNull(UserEmail::fromNative($command->email));

        if (null === $foundUserByEmail) {
            throw ValidationException::withMessages(['email' => 'Пользователь с таким email не существует']);
        }

        if (!Hash::check($command->password, $foundUserByEmail->getPassword()->toNative())) {
            throw ValidationException::withMessages(['password' => 'Неверный пароль']);
        }

        return FindUserResponse::fromUser($foundUserByEmail);
    }
}
