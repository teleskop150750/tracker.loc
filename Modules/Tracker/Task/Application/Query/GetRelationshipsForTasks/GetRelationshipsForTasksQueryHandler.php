<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\GetRelationshipsForTasks;

use App\Support\Arr;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;

class GetRelationshipsForTasksQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly TaskRelationshipRepositoryInterface $taskRelationshipRepository,
    ) {
    }

    public function __invoke(GetRelationshipsForTasksQuery $command): GetRelationshipsForTasksResponse
    {
        $relationships = $this->taskRelationshipRepository->getRelationshipsForTasks($command->taskIds);
        $relationships = Arr::map($relationships, static function ($relationship) {
            $type = $relationship['type'];
            $relationship['source'] = $relationship['leftId'];
            $relationship['target'] = $relationship['rightId'];
            unset($relationship['leftId'], $relationship['rightId']);

            if (TaskRelationshipType::END_START === $type) {
                $relationship['type'] = 0;
            } elseif (TaskRelationshipType::START_START === $type) {
                $relationship['type'] = 1;
            } elseif (TaskRelationshipType::END_END === $type) {
                $relationship['type'] = 2;
            } elseif (TaskRelationshipType::START_END === $type) {
                $relationship['type'] = 3;
            }

            return $relationship;
        });

        return GetRelationshipsForTasksResponse::fromArray($relationships);
    }
}
