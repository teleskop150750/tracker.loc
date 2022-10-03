<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Lumen;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\User\Application\Command\RegisterUser\RegisterUserCommandHandler;
use Modules\Auth\User\Application\Query\FindUser\FindUserQueryHandler;
use Modules\Auth\User\Application\Query\GetAllUsers\GetALlUsersQueryHandler;
use Modules\Auth\User\Application\Query\LoginUser\LoginUserQueryHandler;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Auth\User\Infrastructure\Repository\UserRepository;

class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->tag(RegisterUserCommandHandler::class, 'command_handler');
        $this->app->tag(LoginUserQueryHandler::class, 'query_handler');
        $this->app->tag(FindUserQueryHandler::class, 'query_handler');
        $this->app->tag(GetALlUsersQueryHandler::class, 'query_handler');
    }
}
