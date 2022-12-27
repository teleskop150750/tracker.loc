<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskEndDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;

class UpdateTaskEndDateService
{
    public function updateEndDate(Task $task, TaskEndDate $endDate): void
    {
        if ($task->getEndDate()->sameValueAs($endDate)) {
            return;
        }

        $currentEndDateNative = new \DateTimeImmutable($task->getEndDate()->getDateTime()->format('Y-m-d'));
        $endDateNative = new \DateTimeImmutable($endDate->getDateTime()->format('Y-m-d'));
        $diffDays = (int) $currentEndDateNative->diff($endDateNative)->format('%r%a');

        $this->updateEndDateProcess($task, $diffDays);
    }

    private function updateStartDateProcess(Task $task, int $diff): void
    {
        $diffString = $diff >= 0 ? '+'.abs($diff) : '-'.abs($diff);

        $currentStartDateNative = $task->getStartDate()->getDateTime();
        $currentStartDateNative = $currentStartDateNative->setTime(0, 0, 0, 0);
        $newStartDate = $currentStartDateNative->modify("{$diffString} day");
        $task->setStartDate(TaskStartDate::fromNative($newStartDate));
    }

    private function updateEndDateProcess(Task $task, int $diff): void
    {
        $diffString = $diff >= 0 ? '+'.abs($diff) : '-'.abs($diff);

        $currentEndDateNative = $task->getEndDate()->getDateTime();
        $currentEndDateNative = $currentEndDateNative->setTime(0, 0, 0, 0);
        $newEndDate = $currentEndDateNative->modify("{$diffString} day");
        $this->setEndDate($task, TaskEndDate::fromNative($newEndDate));

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

    /**
     * @return TaskRelationship[]
     */
    private function getInverseRelationships(Task $task): array
    {
        return $task->getInverseTaskRelationships()->toArray();
    }
}
