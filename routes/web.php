<?php

declare(strict_types=1);

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->get('test', 'App\Http\Controllers\ExampleController@test');
$router->get('main', 'App\Http\Controllers\ExampleController@userToArr');


$router->get('tasks-created-by-me', 'Modules\Tracker\Task\Infrastructure\Api\GetTasksCreatedByMeController@__invoke');
$router->get('assigned-tasks-for-me', 'Modules\Tracker\Task\Infrastructure\Api\GetAssignedTasksForMeController@__invoke');
$router->post('tasks/{taskId}/add-file', 'Modules\Tracker\Task\Infrastructure\Api\TaskFileController@add');
$router->get('task-file/{fileId}', 'Modules\Tracker\Task\Infrastructure\Api\TaskFileController@download');
$router->post('remove-task-file/{fileId}', 'Modules\Tracker\Task\Infrastructure\Api\TaskFileController@remove');

$router->get('gantt-assigned-tasks-for-me', 'Modules\Tracker\Task\Infrastructure\Api\GetGanttAssignedTasksForMeController@__invoke');
$router->get('gantt-tasks-created-by-me', 'Modules\Tracker\Task\Infrastructure\Api\GetGanttTasksCreatedByMeController@__invoke');
$router->get('workspace-gantt-tasks-for-me', 'Modules\Tracker\Task\Infrastructure\Api\GetWorkspaceGanttTasksForMeController@__invoke');
$router->get('shared-gantt-tasks-for-me', 'Modules\Tracker\Task\Infrastructure\Api\GetSharedGanttTasksForMeController@__invoke');

$router->get('tasks-relationships-for-tasks', 'Modules\Tracker\Task\Infrastructure\Api\GetRelationshipsForTasksController@__invoke');

$router->get('archive-for-me', 'Modules\Tracker\Shared\Infrastructure\Api\GetArchiveForMeController@__invoke');

$router->get('search', 'Modules\Tracker\Shared\Infrastructure\Api\SearchController@__invoke');
