<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\TaskAddFile;

use Doctrine\ORM\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Shared\Application\Command\CommandHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Task\Domain\Entity\File\File;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;
use Modules\Tracker\Task\Domain\Services\FileStorageServivce;

class TaskAddFileCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly Request $request,
        private readonly FileStorageServivce $fileStorageServivce,
        private readonly UserFetcherInterface $userFetcher,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly FileRepositoryInterface $fileRepository,
    ) {
    }

    public function __invoke(TaskAddFileCommand $command): TaskAddFileResponse
    {
        $task = $this->getTask($command->taskId);
        $response = $this->saveFile($task, $command->file);

        return TaskAddFileResponse::createFromResponse($response);
    }

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

    private function saveFile(Task $task, UploadedFile $file): \Illuminate\Http\Client\Response
    {
        $response = $this->fileStorageServivce->save($file, $this->request->all());
        
        if ($response->status() === 201) {
            $fileId = $response->json('data.id');
            $file = new File(
                FileUuid::fromNative($fileId),
                $task,
            );
            $this->fileRepository->save($file);
        }

        return $response;
    }
}
