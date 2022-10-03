<?php

declare(strict_types=1);

namespace Modules\Tracker\Shared\Infrastructure\Lumen;

use Illuminate\Support\ServiceProvider;
use Modules\Tracker\Shared\Application\Query\GetArchiveForMe\GetArchiveForMeQueryHandler;

class LumenServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->tag(GetArchiveForMeQueryHandler::class, 'query_handler');
    }
}
