<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskEndDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;

class UpdateTaskStartDateService
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

    public function updateStartDate(TaskStartDate $startDate): void
    {
        if ($this->task->getStartDate()->sameValueAs($startDate)) {
            return;
        }

        $currentStartDateNative = new \DateTimeImmutable($this->task->getStartDate()->getDateTime()->format('Y-m-d'));
        $startDateNative = new \DateTimeImmutable($startDate->getDateTime()->format('Y-m-d'));
        $diffDays = $currentStartDateNative->diff($startDateNative)->format('%r%a');

        $this->updateStartDateProcess($this->task, $diffDays);
    }

    private function updateStartDateProcess(Task $task, string $diff): void
    {
        $diffNumber = (int) $diff;
        $diffString = $diffNumber >= 0 ? '+'.abs($diffNumber) : '-'.abs($diffNumber);

        $currentStartDateNative = $task->getStartDate()->getDateTime();
        $currentStartDateNative = $currentStartDateNative->setTime(0, 0, 0, 0);
        $newStartDate = $currentStartDateNative->modify("{$diffString} day");
        $task->setStartDate(TaskStartDate::fromNative($newStartDate));

//        if (false === $this->task->getPublished()->toNative()) {
//            return;
//        }

        $startStartRelationships = $this->filterRelations(
            $this->getInverseRelationships($task),
            [TaskRelationshipType::fromNative(TaskRelationshipType::START_START)]
        );

        foreach ($startStartRelationships as $relationship) {
            $left = $relationship->getLeft();
            $this->updateStartDateProcess($left, $diff);
            $this->updateEndDateProcess($left, $diff);
        }

        $startEndRelationships = $this->filterRelations(
            $this->getInverseRelationships($task),
            [TaskRelationshipType::fromNative(TaskRelationshipType::START_END)]
        );

        foreach ($startEndRelationships as $relationship) {
            $left = $relationship->getLeft();
            $this->updateEndDateProcess($left, $diff);
        }
    }

    private function updateEndDateProcess(Task $task, string $diff): void
    {
        $diffNumber = (int) $diff;
        $diffString = $diffNumber >= 0 ? '+'.abs($diffNumber) : '-'.abs($diffNumber);

        $currentEndDateNative = $task->getEndDate()->getDateTime();
        $currentEndDateNative = $currentEndDateNative->setTime(0, 0, 0, 0);
        $newEndDate = $currentEndDateNative->modify("{$diffString} day");
        $this->setEndDate($task, TaskEndDate::fromNative($newEndDate));

//        if (false === $this->task->getPublished()->toNative()) {
//            return;
//        }

        $endStartRelationships = $this->filterRelations(
            $this->getInverseRelationships($task),
            [
                TaskRelationshipType::fromNative(TaskRelationshipType::END_START),
            ],
        );

        foreach ($endStartRelationships as $relationship) {
            $left = $relationship->getLeft();
            $this->updateStartDateProcess($left, $diff);
            $this->updateEndDateProcess($left, $diff);
        }

        $endEndRelationships = $this->filterRelations(
            $this->getInverseRelationships($task),
            [
                TaskRelationshipType::fromNative(TaskRelationshipType::END_END),
            ],
        );

        foreach ($endEndRelationships as $relationship) {
            $left = $relationship->getLeft();
            $this->updateEndDateProcess($left, $diff);
        }
    }

    /**
     * @return TaskRelationship[]
     */
    private function getInverseRelationships(Task $task): array
    {
        return $task->getInverseTaskRelationships()->toArray();
    }

    /**
     * @param TaskRelationship[]     $relationships
     * @param TaskRelationshipType[] $types
     *
     * @return TaskRelationship[]
     */
    private function filterRelations(array $relationships, array $types): array
    {
        $result = [];

        foreach ($relationships as $relationship) {
            $type = $relationship->getType();
            if (!$this->checkType($type, $types)) {
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

    private function setEndDate(Task $task, TaskEndDate $taskEndDate): void
    {
        $task->setEndDate($taskEndDate);

        if ($task->getEndDate()->getDateTime()->getTimestamp() < $task->getStartDate()->getDateTime()->getTimestamp()) {
            $newStartDate = TaskStartDate::fromNative(clone $taskEndDate->getDateTime());
            $task->setStartDate($newStartDate);
        }
    }
}
