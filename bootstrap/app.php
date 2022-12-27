<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use App\Http\Middleware\ValidateSignature;
use Fruitcake\Cors\CorsServiceProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Mail\MailServiceProvider;
use LaravelDoctrine\ORM\DoctrineServiceProvider;
use Modules\Auth\User\Infrastructure\Lumen\UserServiceProvider;
use Modules\Shared\Infrastructure\Lumen\SharedServiceProvider;
use Modules\Tracker\Folder\Infrastructure\Lumen\FolderServiceProvider;
use Modules\Tracker\Shared\Infrastructure\Lumen\LumenServiceProvider;
use Modules\Tracker\Task\Infrastructure\Lumen\TaskServiceProvider;

require_once __DIR__.'/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))->bootstrap();

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades(
    true,
    [
        LaravelDoctrine\ORM\Facades\EntityManager::class => 'EntityManager',
        LaravelDoctrine\ORM\Facades\Registry::class => 'Registry',
        LaravelDoctrine\ORM\Facades\Doctrine::class => 'Doctrine',
    ]
);

// $app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    ExceptionHandler::class,
    Handler::class
);

$app->singleton(
    Kernel::class,
    \App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Config Files
|--------------------------------------------------------------------------
|
| Now we will register the "app" configuration file. If the file exists in
| your configuration directory it will be loaded; otherwise, we'll load
| the default version. You may register other files below as needed.
|
*/

$app->configure('app');
$app->configure('cors');
$app->configure('hashing');
$app->configure('mail');
$app->configure('filesystems');

$app->alias('mail.manager', Illuminate\Mail\MailManager::class);
$app->alias('mail.manager', Illuminate\Contracts\Mail\Factory::class);

$app->alias('mailer', Illuminate\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\Mailer::class);
$app->alias('mailer', Illuminate\Contracts\Mail\MailQueue::class);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//     App\Http\Middleware\ExampleMiddleware::class
// ]);

$app->middleware([
    Fruitcake\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
    'signed' => ValidateSignature::class,
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

$app->register(\App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);

$app->register(SharedServiceProvider::class);
$app->register(LumenServiceProvider::class);
$app->register(UserServiceProvider::class);
$app->register(FolderServiceProvider::class);
$app->register(TaskServiceProvider::class);
$app->register(CorsServiceProvider::class);
$app->register(DoctrineServiceProvider::class);
$app->register(MailServiceProvider::class);
$app->register(FilesystemServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group(['prefix' => '/api/v1'], function ($router): void {
    require __DIR__.'/../routes/auth.php';

    require __DIR__.'/../routes/user.php';

    require __DIR__.'/../routes/folders.php';

    require __DIR__.'/../routes/tasks.php';

    require __DIR__.'/../routes/web.php';
});

return $app;
