<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\DeleteTask;

use Doctrine\ORM\QueryBuilder;
use Illuminate\Support\Facades\Storage;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Repository\Exceptions\TaskNotFoundException;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class DeleteTaskCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    public function __invoke(DeleteTaskCommand $command): void
    {
        try {
            $task = $this->getTask($command->id);

//            foreach ($task->getFiles() as $file) {
//                Storage::delete($file->getPath()->toNative());
//            }

            $this->taskRepository->remove($task);
        } catch (TaskNotFoundException $exception) {
        }
    }

    /**
     * @throws TaskNotFoundException
     */
    private function getTask(string $id): Task
    {
        $auth = $this->userFetcher->getAuthUser();

        $filter = static function (QueryBuilder $qb) use ($auth, $id): QueryBuilder {
            return $qb->andWhere('t.uuid = :id')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('a.uuid', ':userId'),
                    $qb->expr()->eq('e.uuid', ':userId')
                ))
                ->setParameter('id', $id)
                ->setParameter('userId', $auth->getUuid()->getId());
        };

        return $this->taskRepository->getTask($filter);
    }
}
