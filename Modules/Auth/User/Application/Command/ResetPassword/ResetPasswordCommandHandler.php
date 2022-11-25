<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\ResetPassword;

use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Domain\Entity\ValueObject\PasswordToken;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPassword;
use Modules\Auth\User\Domain\Repository\PasswordResetsRepositoryInterface;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Auth\User\Domain\Services\EmailVerification;
use Modules\Shared\Application\Command\CommandHandlerInterface;

class ResetPasswordCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordResetsRepositoryInterface $passwordResetRepository,
        private readonly EmailVerification $emailVerification,
    ) {
    }

    public function __invoke(ResetPasswordCommand $command): void
    {
        $token = PasswordToken::fromNative($command->token);
        $passwordResets = $this->passwordResetRepository->findByEmailOrNull(UserEmail::fromNative($command->email));

        if (null === $passwordResets || $passwordResets->getToken()->isNotEqualTo($token)) {
            throw ValidationException::withMessages(['email' => 'Неверный email']);
        }

        $user = $this->userRepository->findByEmailOrNull(UserEmail::fromNative($command->email));

        $user->setPassword(UserPassword::fromPlainText($command->password));
        $this->passwordResetRepository->remove($passwordResets);
    }
}
