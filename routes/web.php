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

$router->get('test', 'App\Http\Controllers\ExampleController@test');
$router->get('main', 'App\Http\Controllers\ExampleController@userToArr');

$router->post('register', 'Modules\Auth\User\Infrastructure\Api\RegisterUserController@__invoke');
$router->post('login', 'Modules\Auth\User\Infrastructure\Api\LoginUserController@__invoke');
$router->post('update-me', 'Modules\Auth\User\Infrastructure\Api\UpdateUserController@updateInfo');
$router->post('change-password', 'Modules\Auth\User\Infrastructure\Api\UpdateUserController@updatePassword');
$router->post('email/verification-notification', [
    'as' => 'verification.send',
    'uses' => 'Modules\Auth\User\Infrastructure\Api\RegisterUserController@resendVerificationNotification',
]);
$router->post('email/verify/{id}/{hash}', [
    'middleware' => ['signed'],
    'as' => 'verification.verify',
    'uses' => 'Modules\Auth\User\Infrastructure\Api\RegisterUserController@verifyEmail',
]);
$router->post('forgot-password', ['uses' => 'Modules\Auth\User\Infrastructure\Api\RegisterUserController@forgotPassword']);
$router->post('reset-password/{token}', ['uses' => 'Modules\Auth\User\Infrastructure\Api\RegisterUserController@resetPassword']);

$router->get('users', 'Modules\Auth\User\Infrastructure\Api\GetAllUsersController@__invoke');

$router->post('create-folder', 'Modules\Tracker\Folder\Infrastructure\Api\CreateFolderController@__invoke');
$router->post('update-folder', 'Modules\Tracker\Folder\Infrastructure\Api\UpdateFolderController@__invoke');
$router->post('delete-folder', 'Modules\Tracker\Folder\Infrastructure\Api\DeleteFolderController@__invoke');
$router->get('folder-info', 'Modules\Tracker\Folder\Infrastructure\Api\GetFolderController@__invoke');
$router->get('available-folders-for-me', 'Modules\Tracker\Folder\Infrastructure\Api\GetAvailableFoldersForMeController@__invoke');
$router->get('workspace-folders-for-me', 'Modules\Tracker\Folder\Infrastructure\Api\GetWorkspaceFoldersForMeController@__invoke');
$router->get('shared-folders-for-me', 'Modules\Tracker\Folder\Infrastructure\Api\GetSharedFoldersForMeController@__invoke');

$router->post('create-task', 'Modules\Tracker\Task\Infrastructure\Api\CreateTaskController@__invoke');
$router->post('update-task', 'Modules\Tracker\Task\Infrastructure\Api\UpdateTaskController@__invoke');
$router->post('delete-task/{id}', 'Modules\Tracker\Task\Infrastructure\Api\DeleteTaskController@__invoke');
$router->get('available-tasks-for-me', 'Modules\Tracker\Task\Infrastructure\Api\GetAvailableTasksForMeController@__invoke');
$router->get('task-info/{id}', 'Modules\Tracker\Task\Infrastructure\Api\GetTaskController@__invoke');
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
