<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\ExtendTasks;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskEndDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class ExtendTasksCommandHandler implements CommandHandlerInterface
{
    private DateTimeImmutable $toDay;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskRelationshipRepositoryInterface $taskRelationshipRepository,
    ) {
        $this->toDay = (new DateTimeImmutable())->setTime(0, 0, 0, 0);
    }

    public function __invoke(ExtendTasksCommand $command): void
    {
        $tasksIds = [];
        $relationships = $this->taskRelationshipRepository->getExpiredTasksRelationships();
        $relationshipsMap = [];

        foreach ($relationships as $relationship) {
            $left = $relationship->getLeft();
            $right = $relationship->getRight();
            $type = $relationship->getType()->toNative();

            $leftId = $left->getUuid()->getId();
            $rightId = $right->getUuid()->getId();

            $tasksIds[$leftId] = $leftId;
            $tasksIds[$rightId] = $rightId;

            if (!isset($relationshipsMap[$rightId])) {
                $relationshipsMap[$rightId] = [
                    'right' => $right,
                    'lefts' => [],
                ];
            }
            $relationshipsMap[$rightId]['lefts'][] = [
                'left' => $left,
                'type' => $type,
            ];
        }

        foreach ($relationshipsMap as $relationship) {
            $this->relationshipProcess($relationship);
        }

        $tasks = $this->taskRepository->getExpiredTasks($tasksIds);

        foreach ($tasks as $task) {
            $task->setEndDate(TaskEndDate::fromNative($this->toDay));
        }

        $this->em->flush();
    }

    /**
     * @param array{
     *     right: Task,
     *     lefts: array<int, array {
     *          left: Task,
     *          type: string
     *      }>
     *     } $relationship
     */
    private function relationshipProcess(array $relationship): void
    {
        $right = $relationship['right'];

        $currentEndDateNative = $right->getEndDate()->getDateTime()->setTime(0, 0, 0, 0);
        $diffDays = $currentEndDateNative->diff($this->toDay)->format('%r%a');
        $diffDays = '+'.abs((int) $diffDays);
        $right->setEndDate(TaskEndDate::fromNative($this->toDay));

        if (false === $right->getPublished()->toNative()) {
            return;
        }

        foreach ($relationship['lefts'] as $leftMap) {
            $type = $leftMap['type'];
            $left = $leftMap['left'];

            if (false === $left->getPublished()->toNative()) {
                continue;
            }

            if (TaskRelationshipType::END_START === $type) {
                $this->updateLeft($left, $diffDays, true, true);
            } elseif (TaskRelationshipType::END_END === $type) {
                $this->updateLeft($left, $diffDays, false, true);
            }
        }
    }

    private function updateLeft(
        Task $task,
        string $diff,
        bool $changeStartDate = false,
        bool $changeEndDate = false
    ): void {
        if ($changeStartDate) {
            $currentStartDateNative = $task->getStartDate()->getDateTime();
            $currentStartDateNative = $currentStartDateNative->setTime(0, 0, 0, 0);
            $newStartDate = $currentStartDateNative->modify("{$diff} day");
            $task->setStartDate(TaskStartDate::fromNative($newStartDate));
        }

        if ($changeEndDate) {
            $currentEndDateNative = $task->getEndDate()->getDateTime();
            $currentEndDateNative = $currentEndDateNative->setTime(0, 0, 0, 0);
            $newEndDate = $currentEndDateNative->modify("{$diff} day");
            $task->setEndDate(TaskEndDate::fromNative($newEndDate));
        }
    }
}
