<?php

declare(strict_types=1);

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->group(['middleware' => 'auth'], function () use ($router): void {
    $router->get('tasks', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@index');
    $router->get('tasks-author', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@tasksAuthor');
    $router->get('tasks-executor', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@tasksExecutor');
    $router->get('tasks-indefinite', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@tasksIndefinite');
    $router->get('/folder-me/tasks', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@folderMeTasks');
    $router->get('/folder-shared/tasks', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@folderSharedTasks');
    $router->get('/folder/{folderId}/tasks', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@folderTasks');

    $router->post('tasks', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@create');
    $router->get('tasks/{id}', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@show');
    $router->put('tasks/{id}', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@update');

    $router->delete('tasks/{id}', 'Modules\Tracker\Task\Infrastructure\Api\TasksController@delete');
//    $router->get('available-tasks-for-me', 'Modules\Tracker\Task\Infrastructure\Api\GetAvailableTasksForMeController@__invoke');
});
