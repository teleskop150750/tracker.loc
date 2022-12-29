<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use App\Support\Arr;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipUuid;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;

class UpdateTaskRelationsService
{
    private TaskRelationshipRepositoryInterface $taskRelationshipRepository;

    public function __construct(TaskRelationshipRepositoryInterface $taskRelationshipRepository)
    {
        $this->taskRelationshipRepository = $taskRelationshipRepository;
    }

    /**
     * @param Task[] $depends
     */
    public function updateDepends(Task $task, array $depends): void
    {
        $this->removeDepends($task, $depends);
        $this->addNewDepends($task, $depends);
    }

    public function updateAffects(Task $task, array $affects): void
    {
        $this->removeAffects($task, $affects);
        $this->addNewAffects($task, $affects);
    }

    /**
     * @param Task[] $depends
     */
    private function removeDepends(Task $task, array $depends): void
    {
        $currentRelationships = $task->getTaskRelationships()->toArray();

        foreach ($currentRelationships as $relationship) {
            $this->taskRelationshipRepository->remove($relationship);
        }
    }

    /**
     * @param Task[] $depends
     */
    private function addNewDepends(Task $task, array $depends): void
    {
        $currentRelationships = $task->getTaskRelationships()->toArray();
        $ids = Arr::map($currentRelationships, static function (TaskRelationship $relationship): string {
            return $relationship->getRight()->getUuid()->getId();
        });

        foreach ($depends as $depend) {
            $id = $depend->getUuid()->getId();

            if (!\in_array($id, $ids, true)) {
                $relationship = new TaskRelationship(
                    TaskRelationshipUuid::generateRandom(),
                    $task,
                    $depend,
                    TaskRelationshipType::fromNative(TaskRelationshipType::END_START),
                );

                $this->taskRelationshipRepository->save($relationship);
            }
        }
    }

    /**
     * @param Task[] $affects
     */
    private function removeAffects(Task $task, array $affects): void
    {
        $currentRelationships = $task->getInverseTaskRelationships()->toArray();

        foreach ($currentRelationships as $relationship) {
            $this->taskRelationshipRepository->remove($relationship);
        }
    }

    /**
     * @param Task[] $affects
     */
    private function addNewAffects(Task $task, array $affects): void
    {
        $currentRelationships = $task->getInverseTaskRelationships()->toArray();
        $ids = Arr::map($currentRelationships, static function (TaskRelationship $relationship): void {
            $relationship->getLeft()->getUuid()->getId();
        });

        foreach ($affects as $affect) {
            $id = $affect->getUuid()->getId();

            if (!\in_array($id, $ids, true)) {
                $relationship = new TaskRelationship(
                    TaskRelationshipUuid::generateRandom(),
                    $affect,
                    $task,
                    TaskRelationshipType::fromNative(TaskRelationshipType::END_START),
                );
                $this->taskRelationshipRepository->save($relationship);
            }
        }
    }
}
