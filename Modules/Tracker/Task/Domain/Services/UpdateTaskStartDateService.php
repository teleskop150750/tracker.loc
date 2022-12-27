<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;

class UpdateTaskStartDateService
{
    public function updateStartDate(Task $task, TaskStartDate $startDate): void
    {
        if ($task->getStartDate()->sameValueAs($startDate)) {
            return;
        }

        $currentStartDateNative = new \DateTimeImmutable($task->getStartDate()->getDateTime()->format('Y-m-d'));
        $startDateNative = new \DateTimeImmutable($startDate->getDateTime()->format('Y-m-d'));
        $diffDays = (int) $currentStartDateNative->diff($startDateNative)->format('%r%a');

        $this->updateStartDateProcess($task, $diffDays);
    }

    private function updateStartDateProcess(Task $task, int $diff): void
    {
        $diffString = $diff >= 0 ? '+'.abs($diff) : '-'.abs($diff);

        $currentStartDateNative = $task->getStartDate()->getDateTime();
        $currentStartDateNative = $currentStartDateNative->setTime(0, 0, 0, 0);
        $newStartDate = $currentStartDateNative->modify("{$diffString} day");
        $task->setStartDate(TaskStartDate::fromNative($newStartDate));
    }
}
