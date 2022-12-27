<?php

declare(strict_types=1);

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->group(['middleware' => 'auth'], function () use ($router): void {
    $router->patch('me', 'Modules\Auth\User\Infrastructure\Api\UpdateUserController@updateInfo');
    $router->get('users', 'Modules\Auth\User\Infrastructure\Api\UserController@index');
});
