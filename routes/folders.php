<?php

declare(strict_types=1);

/** @var Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Laravel\Lumen\Routing\Router;

$router->group(['middleware' => 'auth'], function () use ($router): void {
    $router->get('folders', 'Modules\Tracker\Folder\Infrastructure\Api\FoldersController@index');
    $router->post('folders', 'Modules\Tracker\Folder\Infrastructure\Api\FoldersController@create');
    $router->get('folders/{id}', 'Modules\Tracker\Folder\Infrastructure\Api\FoldersController@show');
    $router->put('folders/{id}', 'Modules\Tracker\Folder\Infrastructure\Api\FoldersController@update');
    $router->delete('folders/{id}', 'Modules\Tracker\Folder\Infrastructure\Api\FoldersController@delete');
});
