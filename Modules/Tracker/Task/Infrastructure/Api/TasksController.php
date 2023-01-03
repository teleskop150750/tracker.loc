<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Api;

use App\Exceptions\HttpException;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Tracker\Task\Application\Command\CreateTask\CreateTaskCommand;
use Modules\Tracker\Task\Application\Command\DeleteTask\DeleteTaskCommand;
use Modules\Tracker\Task\Application\Command\UpdateTask\UpdateTaskCommand;
use Modules\Tracker\Task\Application\Query\GetFolderMeTasks\GetFolderMeTasksQuery;
use Modules\Tracker\Task\Application\Query\GetFolderSharedTasks\GetFolderSharedTasksQuery;
use Modules\Tracker\Task\Application\Query\GetFolderTasks\GetFolderTasksQuery;
use Modules\Tracker\Task\Application\Query\GetTask\GetTaskQuery;
use Modules\Tracker\Task\Application\Query\GetTasks\GetTasksQuery;
use Modules\Tracker\Task\Application\Query\GetTasksAuthor\GetTasksAuthorQuery;
use Modules\Tracker\Task\Application\Query\GetTasksExecutor\GetTasksExecutorQuery;
use Modules\Tracker\Task\Application\Query\GetTasksIndefinite\GetTasksIndefiniteQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

class TasksController extends ApiController
{
    public function index(): JsonResponse
    {
        $data = $this->ask(GetTasksQuery::make())->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Доступные задачи',
            'data' => $data,
        ]);
    }

    public function tasksAuthor(): JsonResponse
    {
        $data = $this->ask(GetTasksAuthorQuery::make())->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Задачи автора',
            'data' => $data,
        ]);
    }

    public function tasksExecutor(): JsonResponse
    {
        $data = $this->ask(GetTasksExecutorQuery::make())->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Поставленные задачи',
            'data' => $data,
        ]);
    }

    public function tasksIndefinite(): JsonResponse
    {
        $data = $this->ask(GetTasksIndefiniteQuery::make())->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Нераспределенные задачи',
            'data' => $data,
        ]);
    }

    public function folderMeTasks(): JsonResponse
    {
        $data = $this->ask(GetFolderMeTasksQuery::make())->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Все задачи автора',
            'data' => $data,
        ]);
    }

    public function folderSharedTasks(): JsonResponse
    {
        $data = $this->ask(GetFolderSharedTasksQuery::make())->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Все доступные задачи',
            'data' => $data,
        ]);
    }

    public function folderTasks(string $folderId): JsonResponse
    {
        $this->validate(
            ['id' => $folderId],
            ['id' => ['required', 'uuid']]
        );

        $data = $this->ask(GetFolderTasksQuery::createFromArray(['folderId' => $folderId]))->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Задачи папки',
            'data' => $data,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $taskData = $this->validate(
            ['id' => $id],
            ['id' => ['required', 'uuid']]
        );

        $data = $this->ask(GetTaskQuery::createFromArray($taskData))->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Задача',
            'data' => $data,
        ]);
    }

    /**
     * @throws ConnectionException
     * @throws HttpException
     */
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $taskData = $this->validate(
                $request->all(),
                [
                    'id' => ['required', 'uuid'],
                    'name' => ['required'],
                    'folders' => ['nullable', 'array'],
                    'status' => ['required'],
                    'importance' => ['required'],
                    'startDate' => ['required'],
                    'endDate' => ['required'],
                    'description' => ['nullable'],
                    'executors' => ['nullable', 'array'],
                    'depends' => ['nullable', 'array'],
                    'affects' => ['nullable', 'array'],
                ]
            );

            $this->dispatch(CreateTaskCommand::createFromArray($taskData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Задача создана',
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }

    /**
     * @throws ConnectionException
     */
    public function update(Request $request, EntityManagerInterface $em, string $id): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $taskData = $this->validate(
                [...$request->all(), 'id' => $id],
                [
                    'id' => ['required', 'uuid'],
                    'name' => ['nullable'],
                    'folders' => ['nullable', 'array'],
                    'status' => ['nullable'],
                    'importance' => ['nullable'],
                    'startDate' => ['nullable'],
                    'endDate' => ['nullable'],
                    'description' => ['nullable'],
                    'executors' => ['nullable', 'array'],
                    'depends' => ['nullable', 'array'],
                    'affects' => ['nullable', 'array'],
                ]
            );

            $this->dispatch(UpdateTaskCommand::createFromArray($taskData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Задача обновлена',
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }

    /**
     * @throws ConnectionException
     */
    public function delete(EntityManagerInterface $em, string $id): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $this->validate(
                ['id' => $id],
                ['id' => ['required', 'uuid']]
            );

            $this->dispatch(DeleteTaskCommand::createFromArray(['id' => $id]));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Задача удалена',
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }
}
