<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\RegisterUser;

use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Domain\Entity\Events\RegisterUserEvent;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserDepartment;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Auth\User\Domain\Entity\ValueObject\UserFullName;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPassword;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPhone;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPost;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;

class RegisterUserCommandHandler implements CommandHandlerInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        $foundUserByEmail = $this->userRepository->findByEmailOrNull(UserEmail::fromNative($command->email));

        if (null !== $foundUserByEmail) {
            throw ValidationException::withMessages(['email' => 'Пользователь с таким email уже существует']);
        }

        $user = new User(
            UserUuid::fromNative($command->id),
            UserEmail::fromNative($command->email),
            UserFullName::fromNative(
                $command->firstName,
                $command->lastName,
                $command->patronymic
            ),
            UserPassword::fromPlainText($command->password),
        );

        $user->setPhone(UserPhone::fromNative($command->phone));
        $user->setPost(UserPost::fromNative($command->post));
        $user->setDepartment(UserDepartment::fromNative($command->department));

        $this->userRepository->save($user);

        Event::dispatch(new RegisterUserEvent($user));
    }
}
