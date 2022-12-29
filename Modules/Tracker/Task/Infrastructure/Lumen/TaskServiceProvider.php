<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Lumen;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\Tracker\Task\Application\Command\CreateTask\CreateTaskCommandHandler;
use Modules\Tracker\Task\Application\Command\DeleteTask\DeleteTaskCommandHandler;
use Modules\Tracker\Task\Application\Command\ExtendTasks\ExtendTasksCommandHandler;
use Modules\Tracker\Task\Application\Command\TaskAddFile\TaskAddFileCommandHandler;
use Modules\Tracker\Task\Application\Command\TaskRemoveFile\TaskRemoveFileCommandHandler;
use Modules\Tracker\Task\Application\Command\UpdateTask\UpdateTaskCommandHandler;
use Modules\Tracker\Task\Application\Query\DownloadFile\DownloadFileQueryHandler;
use Modules\Tracker\Task\Application\Query\GetFolderMeTasks\GetFolderMeTasksQueryHandler;
use Modules\Tracker\Task\Application\Query\GetFolderSharedTasks\GetFolderSharedTasksQueryHandler;
use Modules\Tracker\Task\Application\Query\GetFolderTasks\GetFolderTasksQueryHandler;
use Modules\Tracker\Task\Application\Query\GetTask\GetTaskQueryHandler;
use Modules\Tracker\Task\Application\Query\GetTasks\GetTasksQueryHandler;
use Modules\Tracker\Task\Application\Query\GetTasksAuthor\GetTasksAuthorQueryHandler;
use Modules\Tracker\Task\Application\Query\GetTasksExecutor\GetTasksExecutorQueryHandler;
use Modules\Tracker\Task\Application\Query\GetTasksIndefinite\GetTasksIndefiniteQueryHandler;
use Modules\Tracker\Task\Domain\Entity\Task\Events\NewExecutorEvent;
use Modules\Tracker\Task\Domain\Entity\Task\Events\NewExecutorHandler;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;
use Modules\Tracker\Task\Infrastructure\Repository\FileRepository;
use Modules\Tracker\Task\Infrastructure\Repository\TaskRelationshipRepository;
use Modules\Tracker\Task\Infrastructure\Repository\TaskRepository;

class TaskServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRelationshipRepositoryInterface::class, TaskRelationshipRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);

        $this->app->tag(CreateTaskCommandHandler::class, 'command_handler');
        $this->app->tag(UpdateTaskCommandHandler::class, 'command_handler');
        $this->app->tag(DeleteTaskCommandHandler::class, 'command_handler');
        $this->app->tag(ExtendTasksCommandHandler::class, 'command_handler');

        $this->app->tag(TaskAddFileCommandHandler::class, 'command_handler');
        $this->app->tag(TaskRemoveFileCommandHandler::class, 'command_handler');

        $this->app->tag(GetTaskQueryHandler::class, 'query_handler');
        $this->app->tag(GetTasksQueryHandler::class, 'query_handler');
        $this->app->tag(GetTasksAuthorQueryHandler::class, 'query_handler');
        $this->app->tag(GetTasksExecutorQueryHandler::class, 'query_handler');
        $this->app->tag(GetTasksIndefiniteQueryHandler::class, 'query_handler');
        $this->app->tag(GetFolderMeTasksQueryHandler::class, 'query_handler');
        $this->app->tag(GetFolderSharedTasksQueryHandler::class, 'query_handler');
        $this->app->tag(GetFolderTasksQueryHandler::class, 'query_handler');
//        $this->app->tag(SearchTasksQueryHandler::class, 'query_handler');
//        $this->app->tag(GetTasksCreatedByMeQueryHandler::class, 'query_handler');
//        $this->app->tag(GetTasksCreatedByMeQueryHandler::class, 'query_handler');
//        $this->app->tag(GetAssignedTasksForMeQueryHandler::class, 'query_handler');

//        $this->app->tag(GetWorkspaceGanttTasksForMeQueryHandler::class, 'query_handler');
//        $this->app->tag(GetGanttTasksCreatedByMeQueryHandler::class, 'query_handler');
//        $this->app->tag(GetGanttAssignedTasksForMeQueryHandler::class, 'query_handler');
//        $this->app->tag(GetSharedGanttTasksForMeQueryHandler::class, 'query_handler');

//        $this->app->tag(GetRelationshipsForTasksQueryHandler::class, 'query_handler');

        $this->app->tag(DownloadFileQueryHandler::class, 'query_handler');
    }

    public function boot(): void
    {
        Event::listen(NewExecutorEvent::class, NewExecutorHandler::class);
    }
}
