<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Auth\User\Domain\Services\UserFormatter;
use Modules\Shared\Domain\ValueObject\DateTime\DateTime;

class TaskFormatter
{
    public static function make(): static
    {
        return new static();
    }

    public function formatDqlTasks(array $tasks = [], $parentId = null): array
    {
        $result = [];
        foreach ($tasks as $task) {
            $result[] = $this->formatDqlTask($task, $parentId);
        }

        return $result;
    }

    public function formatDqlTask(array $task = [], $parentId = null): array
    {
        $startDateNative = $task['startDate.value'];
        $endDateNative = $task['endDate.value'];
        $diffDays = $startDateNative->diff($endDateNative)->format('%r%a');
        $diffDays = abs((int) $diffDays) + 1;
        $result = [
            'id' => $task['uuid']->getId(),
            'parentId' => $parentId,
            'name' => $task['name.value'],
            'published' => $task['published.value'],
            'wasStarted' => $task['wasStarted.value'],
            'author' => UserFormatter::make()->formatDQLUser($task['author']),
            'startDate' => $task['startDate.value']->format(DateTime::FRONTEND_FORMAT),
            'endDate' => $task['endDate.value']->format(DateTime::FRONTEND_FORMAT),
            'importance' => $task['importance.value'],
            'status' => $task['status.value'],
            'createdAt' => $task['createdAt']->format(DateTime::FRONTEND_FORMAT),
            'executors' => UserFormatter::make()->formatDQLUsers($task['executors']),
            'sharedUsers' => [],
            'entityType' => 'TASK',
            'duration' => $diffDays,
        ];

        if (isset($task['files'])) {
            $result['files'] = $this->formatFiles($task['files']);
        }

        if (isset($task['taskRelationships'])) {
            $result['relationships'] = $this->formatRelations($task['taskRelationships']);
        }

        return $result;
    }

    private function formatRelations(array $relations): array
    {
        $result = [];

        foreach ($relations as $relation) {
            if (null === $relation['right']) {
                continue;
            }

            $result[] = [
                'id' => $relation['uuid']->getId(),
                'task' => $this->formatDqlTask($relation['right']),
                'type' => $relation['type.value'],
            ];
        }

        return $result;
    }

    private function formatFiles(array $files): array
    {
        $result = [];

        foreach ($files as $file) {
            $result[] = $this->formatFile($file);
        }

        return $result;
    }

    private function formatFile(array $file): array
    {
        return [
            'id' => $file['uuid']->getId(),
            'originName' => $file['originName.value'],
            'path' => $file['path.value'],
            'createdAt' => $file['createdAt']->format(DateTime::FRONTEND_FORMAT),
        ];
    }
}
