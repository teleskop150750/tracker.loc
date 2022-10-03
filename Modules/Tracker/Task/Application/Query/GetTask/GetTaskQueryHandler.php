<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetTask;

use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetTaskQueryHandler implements QueryHandlerInterface
{
    private Task $task;

    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function __invoke(GetTaskQuery $command): GetTaskResponse
    {
        $this->task = $this->taskRepository->find(TaskUuid::fromNative($command->id));
        $info = $this->taskRepository->getTaskInfo(TaskUuid::fromNative($command->id));

        $CAN_BEGIN_TASK = $this->canBeginTask();
        $CAN_END_TASK = $this->canEndTask();

        $rights = [
            'CAN_BEGIN_TASK' => $CAN_BEGIN_TASK,
            'CAN_END_TASK' => $CAN_BEGIN_TASK && $CAN_END_TASK,
        ];

        return GetTaskResponse::fromArray([
            'task' => $info,
            'rights' => $rights,
        ]);
    }

    private function canBeginTask(): bool
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

        return \count($relationships) <= 0;
    }

    private function canEndTask(): bool
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

        return \count($relationships) <= 0;
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
