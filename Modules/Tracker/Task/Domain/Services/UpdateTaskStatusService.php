<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Illuminate\Validation\ValidationException;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskWasStarted;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;

class UpdateTaskStatusService
{
    private Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public static function make(Task $task): static
    {
        return new static($task);
    }

    public function updateStatus(TaskStatus $status): void
    {
        $inWorkStatus = TaskStatus::fromNative(TaskStatus::IN_WORK);
        $doneStatus = TaskStatus::fromNative(TaskStatus::DONE);
        $currentStatus = $this->task->getStatus();

        if ($currentStatus->sameValueAs($status)) {
            return;
        }

        if ($status->sameValueAs($inWorkStatus)) {
            $this->setInWorkStatus($inWorkStatus);

            return;
        }

        if ($status->sameValueAs($doneStatus)) {
            $this->setDoneStatus($doneStatus);

            return;
        }

        $this->task->setStatus($status);
    }

    private function setInWorkStatus(TaskStatus $status): void
    {
        $relationships = [
            ...$this->filterRelations(
                $this->getRelations(),
                [TaskRelationshipType::fromNative(TaskRelationshipType::END_START)],
                [
                    TaskStatus::fromNative(TaskStatus::DONE),
                    TaskStatus::fromNative(TaskStatus::CANCELLED),
                ],
            ),
            ...$this->filterRelations(
                $this->getRelations(),
                [TaskRelationshipType::fromNative(TaskRelationshipType::START_START)],
                [
                    TaskStatus::fromNative(TaskStatus::IN_WORK),
                    TaskStatus::fromNative(TaskStatus::DONE),
                    TaskStatus::fromNative(TaskStatus::CANCELLED),
                ],
            ),
        ];

        if (\count($relationships) > 0) {
            throw ValidationException::withMessages(['status' => 'Нельзя начать задачу']);
        }

        $this->task->setWasStarted(TaskWasStarted::fromNative(true));
        $this->task->setStatus($status);
    }

    private function setDoneStatus(TaskStatus $status): void
    {
        $relationships = [
            ...$this->filterRelations(
                $this->getRelations(),
                [TaskRelationshipType::fromNative(TaskRelationshipType::END_END)],
                [
                    TaskStatus::fromNative(TaskStatus::DONE),
                    TaskStatus::fromNative(TaskStatus::CANCELLED),
                ],
            ),
            ...$this->filterRelations(
                $this->getRelations(),
                [TaskRelationshipType::fromNative(TaskRelationshipType::START_END)],
                [
                    TaskStatus::fromNative(TaskStatus::IN_WORK),
                    TaskStatus::fromNative(TaskStatus::DONE),
                    TaskStatus::fromNative(TaskStatus::CANCELLED),
                ],
            ),
        ];

        if (\count($relationships) > 0) {
            throw ValidationException::withMessages(['status' => 'Нельзя закончить задачу']);
        }

        $this->task->setStatus($status);
    }

    /**
     * @return TaskRelationship[]
     */
    private function getRelations(): array
    {
        return $this->task->getTaskRelationships()->toArray();
    }

    /**
     * @param TaskRelationship[]     $relationships
     * @param TaskRelationshipType[] $types
     * @param TaskStatus[]           $statuses
     *
     * @return TaskRelationship[]
     */
    private function filterRelations(array $relationships, array $types, array $statuses): array
    {
        $result = [];

        foreach ($relationships as $relationship) {
            $right = $relationship->getRight();
            $type = $relationship->getType();
            $rightStatus = $right->getStatus();

            if (false === $right->getPublished()->toNative()) {
                continue;
            }

            if (!$this->checkType($type, $types)) {
                continue;
            }

            if ($this->checkTaskStatus($rightStatus, $statuses)) {
                continue;
            }

            $result[] = $relationship;
        }

        return $result;
    }

    /**
     * @param TaskRelationshipType[] $types
     */
    private function checkType(TaskRelationshipType $type, array $types = []): bool
    {
        foreach ($types as $item) {
            if ($item->sameValueAs($type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TaskStatus[] $statuses
     */
    private function checkTaskStatus(TaskStatus $status, array $statuses = []): bool
    {
        foreach ($statuses as $item) {
            if ($item->sameValueAs($status)) {
                return true;
            }
        }

        return false;
    }
}
