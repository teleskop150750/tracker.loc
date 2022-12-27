<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\ForgotPassword;

use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Domain\Entity\Events\ResetPasswordEvent;
use Modules\Auth\User\Domain\Entity\PasswordResets;
use Modules\Auth\User\Domain\Entity\ValueObject\PasswordToken;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Auth\User\Domain\Repository\PasswordResetsRepositoryInterface;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;

class ForgotPasswordCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordResetsRepositoryInterface $passwordResetRepository,
    ) {
    }

    public function __invoke(ForgotPasswordCommand $command): void
    {
        $userEmail = UserEmail::fromNative($command->email);
        $foundUserByEmail = $this->userRepository->findByEmailOrNull($userEmail);

        if (!$foundUserByEmail) {
            throw ValidationException::withMessages(['email' => 'Неверный email']);
        }

        $foundPasswordReset = $this->passwordResetRepository->findByEmailOrNull($userEmail);

        if ($foundPasswordReset) {
            $this->passwordResetRepository->remove($foundPasswordReset);
        }

        $token = PasswordToken::generateRandom();

        $passwordResets = new PasswordResets($userEmail, $token);
        $this->passwordResetRepository->save($passwordResets);

        Event::dispatch(new ResetPasswordEvent($foundUserByEmail, $passwordResets));
    }
}
