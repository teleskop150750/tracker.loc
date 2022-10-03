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

use App\Http\Controllers\ExampleController;
use App\Routing\Router;
use Modules\Auth\User\Infrastructure\Api\GetAllUsersController;
use Modules\Auth\User\Infrastructure\Api\LoginUserController;
use Modules\Auth\User\Infrastructure\Api\RegisterUserController;
use Modules\Tracker\Folder\Infrastructure\Api\CreateFolderController;
use Modules\Tracker\Folder\Infrastructure\Api\DeleteFolderController;
use Modules\Tracker\Folder\Infrastructure\Api\GetAvailableFoldersForMeController;
use Modules\Tracker\Folder\Infrastructure\Api\GetFolderController;
use Modules\Tracker\Folder\Infrastructure\Api\GetSharedFoldersForMeController;
use Modules\Tracker\Folder\Infrastructure\Api\GetWorkspaceFoldersForMeController;
use Modules\Tracker\Folder\Infrastructure\Api\UpdateFolderController;
use Modules\Tracker\Shared\Infrastructure\Api\GetArchiveForMeController;
use Modules\Tracker\Shared\Infrastructure\Api\SearchController;
use Modules\Tracker\Task\Infrastructure\Api\CreateTaskController;
use Modules\Tracker\Task\Infrastructure\Api\DeleteTaskController;
use Modules\Tracker\Task\Infrastructure\Api\ExtendTasksController;
use Modules\Tracker\Task\Infrastructure\Api\GetAssignedTasksForMeController;
use Modules\Tracker\Task\Infrastructure\Api\GetAvailableTasksForMeController;
use Modules\Tracker\Task\Infrastructure\Api\GetGanttAssignedTasksForMeController;
use Modules\Tracker\Task\Infrastructure\Api\GetGanttTasksCreatedByMeController;
use Modules\Tracker\Task\Infrastructure\Api\GetRelationshipsForTasksController;
use Modules\Tracker\Task\Infrastructure\Api\GetSharedGanttTasksForMeController;
use Modules\Tracker\Task\Infrastructure\Api\GetTaskController;
use Modules\Tracker\Task\Infrastructure\Api\GetTasksCreatedByMeController;
use Modules\Tracker\Task\Infrastructure\Api\GetWorkspaceGanttTasksForMeController;
use Modules\Tracker\Task\Infrastructure\Api\UpdateTaskController;

$router->get('test', ['uses' => [ExampleController::class, 'test']]);
$router->get('main', ['uses' => [ExtendTasksController::class, '__invoke']]);

$router->post('register', ['uses' => [RegisterUserController::class, '__invoke']]);
$router->post('login', ['uses' => [LoginUserController::class, '__invoke']]);

$router->get('get-users', ['uses' => [GetAllUsersController::class, '__invoke']]);

$router->post('create-folder', ['uses' => [CreateFolderController::class, '__invoke']]);
$router->post('update-folder', ['uses' => [UpdateFolderController::class, '__invoke']]);
$router->post('delete-folder', ['uses' => [DeleteFolderController::class, '__invoke']]);
$router->get('get-folder-info', ['uses' => [GetFolderController::class, '__invoke']]);
$router->get('get-available-folders-for-me', ['uses' => [GetAvailableFoldersForMeController::class, '__invoke']]);
$router->get('get-workspace-folders-for-me', ['uses' => [GetWorkspaceFoldersForMeController::class, '__invoke']]);
$router->get('get-shared-folders-for-me', ['uses' => [GetSharedFoldersForMeController::class, '__invoke']]);

$router->post('create-task', ['uses' => [CreateTaskController::class, '__invoke']]);
$router->post('update-task', ['uses' => [UpdateTaskController::class, '__invoke']]);
$router->post('delete-task', ['uses' => [DeleteTaskController::class, '__invoke']]);
$router->get('get-available-tasks-for-me', ['uses' => [GetAvailableTasksForMeController::class, '__invoke']]);
$router->get('get-task-info', ['uses' => [GetTaskController::class, '__invoke']]);
$router->get('get-tasks-created-by-me', ['uses' => [GetTasksCreatedByMeController::class, '__invoke']]);
$router->get('get-assigned-tasks-for-me', ['uses' => [GetAssignedTasksForMeController::class, '__invoke']]);

$router->get('get-gantt-assigned-tasks-for-me', ['uses' => [GetGanttAssignedTasksForMeController::class, '__invoke']]);
$router->get('get-gantt-tasks-created-by-me', ['uses' => [GetGanttTasksCreatedByMeController::class, '__invoke']]);
$router->get('get-workspace-gantt-tasks-for-me', ['uses' => [GetWorkspaceGanttTasksForMeController::class, '__invoke']]);
$router->get('get-shared-gantt-tasks-for-me', ['uses' => [GetSharedGanttTasksForMeController::class, '__invoke']]);

$router->get('get-tasks-relationships-for-tasks', ['uses' => [GetRelationshipsForTasksController::class, '__invoke']]);

$router->get('get-archive-for-me', ['uses' => [GetArchiveForMeController::class, '__invoke']]);

$router->get('search', ['uses' => [SearchController::class, '__invoke']]);
