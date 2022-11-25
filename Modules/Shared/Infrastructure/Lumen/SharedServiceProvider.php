<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Lumen;

use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application;
use Modules\Shared\Application\Command\CommandBusInterface;
use Modules\Shared\Application\Query\QueryBusInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Shared\Infrastructure\Bus\CallableFirstParameterExtractor;
use Modules\Shared\Infrastructure\Bus\CommandBus;
use Modules\Shared\Infrastructure\Bus\MessageBus;
use Modules\Shared\Infrastructure\Bus\QueryBus;
use Modules\Shared\Infrastructure\Security\UserFetcher;

final class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserFetcherInterface::class, UserFetcher::class);
        $this->app->bind(
            CommandBusInterface::class,
            function (Application $app) {
                return new CommandBus(
                    new MessageBus(
                        CallableFirstParameterExtractor::forCallables($app->tagged('command_handler')),
                    )
                );
            }
        );
        $this->app->bind(
            QueryBusInterface::class,
            function (Application $app) {
                return new QueryBus(
                    new MessageBus(
                        CallableFirstParameterExtractor::forCallables($app->tagged('query_handler')),
                    )
                );
            }
        );
    }
}
