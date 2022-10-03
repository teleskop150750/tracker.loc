<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Services;

use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;

class UpdateTaskRelationsService
{
    private Task $task;
    private TaskRelationshipRepositoryInterface $taskRelationshipRepository;

    public function __construct(Task $task, TaskRelationshipRepositoryInterface $taskRelationshipRepository)
    {
        $this->task = $task;
        $this->taskRelationshipRepository = $taskRelationshipRepository;
    }

    public static function make(Task $task, TaskRelationshipRepositoryInterface $taskRelationshipRepository): static
    {
        return new static($task, $taskRelationshipRepository);
    }

    /**
     * @param TaskRelationship[] $relationships
     */
    public function updateRelations(array $relationships): void
    {
        $newRelationshipsById = [];

        foreach ($relationships as $relationship) {
            $newRelationshipsById[$relationship->getUuid()->getId()] = $relationship;
        }

        $currentRelationships = $this->getCurrentRelations();

        $currentRelationshipsById = [];

        foreach ($currentRelationships as $relationship) {
            $currentRelationshipsById[$relationship->getUuid()->getId()] = $relationship;
        }

        $this->removeRelationships($currentRelationships, $newRelationshipsById);
        $this->addNewRelationships($currentRelationshipsById, $relationships);
    }

    /**
     * @param TaskRelationship[]              $currentRelationships
     * @param array<string, TaskRelationship> $newRelationshipsById
     */
    private function removeRelationships(array $currentRelationships, array $newRelationshipsById): void
    {
        $diffRelationshipsForRemove = [];

        foreach ($currentRelationships as $relationship) {
            $id = $relationship->getUuid()->getId();

            if (!isset($newRelationshipsById[$id])) {
                $diffRelationshipsForRemove[] = $relationship;
            }
        }

        foreach ($diffRelationshipsForRemove as $item) {
            $this->taskRelationshipRepository->remove($item);
        }
    }

    /**
     * @param array<string, TaskRelationship> $currentRelationshipsById
     * @param TaskRelationship[]              $newRelationships
     */
    private function addNewRelationships(array $currentRelationshipsById, array $newRelationships): void
    {
        $diffRelationshipsForSave = [];

        foreach ($newRelationships as $relationship) {
            $id = $relationship->getUuid()->getId();

            if (!isset($currentRelationshipsById[$id])) {
                $diffRelationshipsForSave[] = $relationship;
            }
        }

        foreach ($diffRelationshipsForSave as $item) {
            $this->taskRelationshipRepository->save($item);
        }
    }

    /**
     * @return TaskRelationship[]
     */
    private function getCurrentRelations(): array
    {
        return $this->task->getTaskRelationships()->toArray();
    }
}
