<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Lumen;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\User\Application\Command\ForgotPassword\ForgotPasswordCommandHandler;
use Modules\Auth\User\Application\Command\RegisterUser\RegisterUserCommandHandler;
use Modules\Auth\User\Application\Command\ResendVerificationNotification\ResendVerificationNotificationCommandHandler;
use Modules\Auth\User\Application\Command\ResetPassword\ResetPasswordCommandHandler;
use Modules\Auth\User\Application\Command\UpdateUser\UpdateUserCommandHandler;
use Modules\Auth\User\Application\Command\UpdateUserPassword\UpdateUserPasswordCommandHandler;
use Modules\Auth\User\Application\Command\VerifyEmail\VerifyEmailCommandHandler;
use Modules\Auth\User\Application\Query\FindUser\FindUserQueryHandler;
use Modules\Auth\User\Application\Query\GetAllUsers\GetALlUsersQueryHandler;
use Modules\Auth\User\Application\Query\LoginUser\LoginUserQueryHandler;
use Modules\Auth\User\Domain\Repository\PasswordResetsRepositoryInterface;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Auth\User\Infrastructure\Repository\PasswordResetsRepository;
use Modules\Auth\User\Infrastructure\Repository\UserRepository;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PasswordResetsRepositoryInterface::class, PasswordResetsRepository::class);
        $this->app->tag(RegisterUserCommandHandler::class, 'command_handler');
        $this->app->tag(ResendVerificationNotificationCommandHandler::class, 'command_handler');
        $this->app->tag(UpdateUserCommandHandler::class, 'command_handler');
        $this->app->tag(UpdateUserPasswordCommandHandler::class, 'command_handler');
        $this->app->tag(ForgotPasswordCommandHandler::class, 'command_handler');
        $this->app->tag(ResetPasswordCommandHandler::class, 'command_handler');

        $this->app->tag(VerifyEmailCommandHandler::class, 'command_handler');
        $this->app->tag(LoginUserQueryHandler::class, 'query_handler');
        $this->app->tag(FindUserQueryHandler::class, 'query_handler');
        $this->app->tag(GetALlUsersQueryHandler::class, 'query_handler');
    }
}
