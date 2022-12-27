<?php

declare(strict_types=1);

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->post('login', 'Modules\Auth\User\Infrastructure\Api\LoginUserController@__invoke');
$router->post('register', 'Modules\Auth\User\Infrastructure\Api\RegisterUserController@register');

$router->post('email/verification-notification', [
    'as' => 'verification.send',
    'uses' => 'Modules\Auth\User\Infrastructure\Api\VerificationController@resendVerificationNotification',
]);
$router->post('email/verify/{id}/{hash}', [
    'middleware' => ['signed'],
    'as' => 'verification.verify',
    'uses' => 'Modules\Auth\User\Infrastructure\Api\VerificationController@verifyEmail',
]);

$router->post('forgot-password', ['uses' => 'Modules\Auth\User\Infrastructure\Api\PasswordController@forgotPassword']);
$router->post('reset-password/{token}', ['uses' => 'Modules\Auth\User\Infrastructure\Api\PasswordController@resetPassword']);

$router->post('change-password', 'Modules\Auth\User\Infrastructure\Api\UpdateUserController@updatePassword');
