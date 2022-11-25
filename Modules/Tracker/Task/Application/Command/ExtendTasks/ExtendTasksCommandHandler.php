<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\ExtendTasks;

use Doctrine\ORM\EntityManagerInterface;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskEndDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class ExtendTasksCommandHandler implements CommandHandlerInterface
{
    private \DateTimeImmutable $toDay;

    /**
     * @var array<string, string>
     */
    private array $tasksIds = [];

    /**
     * @var array<string, string>
     */
    private array $relationshipIds = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly TaskRelationshipRepositoryInterface $taskRelationshipRepository,
    ) {
        $this->toDay = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);
    }

    public function __invoke(ExtendTasksCommand $command): void
    {
        $relationships = $this->taskRelationshipRepository->getExpiredTasksRelationships();
        $rightIds = [];

        foreach ($relationships as $relationship) {
            $relationshipId = $relationship->getUuid()->getId();

            if (isset($this->relationshipIds[$relationshipId])) {
                continue;
            }

            $right = $relationship->getRight();
            $rightId = $right->getUuid()->getId();

            if (isset($rightIds[$rightId])) {
                continue;
            }

            $rightIds[$rightId] = $rightId;
            $currentEndDateNative = $right->getEndDate()->getDateTime()->setTime(0, 0, 0, 0);
            $diffDays = $currentEndDateNative->diff($this->toDay)->format('%r%a');
            $diffDays = '+'.abs((int) $diffDays);
            $this->updateEndDateProcess($right, $diffDays);
        }

        $tasks = $this->taskRepository->getExpiredTasks($this->tasksIds);

        foreach ($tasks as $task) {
            $task->setEndDate(TaskEndDate::fromNative($this->toDay));
        }

        $this->em->flush();
    }

    private function updateStartDateProcess(Task $task, string $diff): void
    {
        $taskId = $task->getUuid()->getId();
        $this->tasksIds[$taskId] = $taskId;
        $diffNumber = (int) $diff;
        $diffString = $diffNumber >= 0 ? '+'.abs($diffNumber) : '-'.abs($diffNumber);

        $currentStartDateNative = $task->getStartDate()->getDateTime();
        $currentStartDateNative = $currentStartDateNative->setTime(0, 0, 0, 0);
        $newStartDate = $currentStartDateNative->modify("{$diffString} day");
        $task->setStartDate(TaskStartDate::fromNative($newStartDate));

//        if (false === $task->getPublished()->toNative()) {
//            return;
//        }

        $startStartRelationships = $this->filterRelations(
            $this->getInverseRelationships($task),
            [TaskRelationshipType::fromNative(TaskRelationshipType::START_START)]
        );

        foreach ($startStartRelationships as $relationship) {
            $relationshipId = $relationship->getUuid()->getId();
            $this->relationshipIds[$relationshipId] = $relationshipId;
            $left = $relationship->getLeft();
            $this->updateStartDateProcess($left, $diff);
            $this->updateEndDateProcess($left, $diff);
        }

        $startEndRelationships = $this->filterRelations(
            $this->getInverseRelationships($task),
            [TaskRelationshipType::fromNative(TaskRelationshipType::START_END)]
        );

        foreach ($startEndRelationships as $relationship) {
            $relationshipId = $relationship->getUuid()->getId();
            $this->relationshipIds[$relationshipId] = $relationshipId;
            $left = $relationship->getLeft();
            $this->updateEndDateProcess($left, $diff);
        }
    }

    private function updateEndDateProcess(Task $task, string $diff): void
    {
        $taskId = $task->getUuid()->getId();
        $this->tasksIds[$taskId] = $taskId;
        $diffNumber = (int) $diff;
        $diffString = $diffNumber >= 0 ? '+'.abs($diffNumber) : '-'.abs($diffNumber);

        $currentEndDateNative = $task->getEndDate()->getDateTime();
        $currentEndDateNative = $currentEndDateNative->setTime(0, 0, 0, 0);
        $newEndDate = $currentEndDateNative->modify("{$diffString} day");
        $this->setEndDate($task, TaskEndDate::fromNative($newEndDate));

//        if (false === $task->getPublished()->toNative()) {
//            return;
//        }

        $endStartRelationships = $this->filterRelations(
            $this->getInverseRelationships($task),
            [TaskRelationshipType::fromNative(TaskRelationshipType::END_START)],
        );

        foreach ($endStartRelationships as $relationship) {
            $relationshipId = $relationship->getUuid()->getId();
            $this->relationshipIds[$relationshipId] = $relationshipId;
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
            $relationshipId = $relationship->getUuid()->getId();
            $this->relationshipIds[$relationshipId] = $relationshipId;
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
