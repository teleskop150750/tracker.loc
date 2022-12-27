<?php

declare(strict_types=1);

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->get('test', 'App\Http\Controllers\ExampleController@test');
$router->get('main', 'App\Http\Controllers\ExampleController@userToArr');

$router->post('tasks/{taskId}/add-file', 'Modules\Tracker\Task\Infrastructure\Api\TaskFileController@add');
$router->get('task-file/{fileId}', 'Modules\Tracker\Task\Infrastructure\Api\TaskFileController@download');
$router->post('remove-task-file/{fileId}', 'Modules\Tracker\Task\Infrastructure\Api\TaskFileController@remove');
