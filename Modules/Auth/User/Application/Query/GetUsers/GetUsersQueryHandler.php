<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\GetUsers;

use App\Support\Arr;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Shared\Application\Query\QueryHandlerInterface;
use Modules\Shared\Domain\Security\UserFetcherInterface;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;

class GetUsersQueryHandler implements QueryHandlerInterface
{
    public const MAX_NEAREST = 5;
    public const MAX_ALL = 10;

    public function __construct(
        private readonly UserFetcherInterface $userFetcher,
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly FolderRepositoryInterface $folderRepository,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(GetUsersQuery $command): ?GetUsersQueryResponse
    {
        $folderUsers = $this->getFolderUsers($command);
        $tasksUsers = $this->getTasksUsers($command, self::MAX_NEAREST - \count($folderUsers));
        $users = Arr::keyBy([...$folderUsers, ...$tasksUsers], 'id');
        $usersIds = Arr::keys($users);
        $users = Arr::values($users);

        $otherUsers = $this->getOtherUsers($command, $usersIds, self::MAX_ALL - \count($users));
        $users = [...$users, ...$otherUsers];
        $users = Arr::values(Arr::keyBy($users, 'id'));

        return GetUsersQueryResponse::fromArray($users);
    }

    private function getFolderUsers(GetUsersQuery $command, int $max = 5): array
    {
        $auth = $this->userFetcher->getAuthUser();
        $folderIds = $this->folderRepository->getAvailableFoldersIds($auth);

        $foldersFilter = static function (QueryBuilder $qb) use ($folderIds, $command, $max): QueryBuilder {
            $qb = $qb
                ->where('f.id IN (:folderIds)')
                ->setParameter('folderIds', $folderIds)
                ->setMaxResults($max);

            if ($command->search) {
                $qb->leftJoin('f.sharedUsers', 'su', Join::WITH, 'su.email.value LIKE :search');
                $qb->setParameter('search', "%{$command->search}%");
            } else {
                $qb->leftJoin('f.sharedUsers', 'su');
            }

            return $qb;
        };

        $users = $this->folderRepository->getFoldersUsers($foldersFilter);

        return Arr::where($users, static function (array $user) use ($command): bool {
            if (!$command->search) {
                return true;
            }

            return (bool) preg_match("/{$command->search}/", $user['email']);
        });
    }

    private function getTasksUsers(GetUsersQuery $command, int $max = 5): array
    {
        $auth = $this->userFetcher->getAuthUser();
        $tasksIds = $this->taskRepository->getAvailableTasksIds($auth);

        $tasksFilter = static function (QueryBuilder $qb) use ($tasksIds, $command, $max): QueryBuilder {
            $qb = $qb
                ->where('t.uuid IN(:tasksIds)')
                ->setParameter('tasksIds', $tasksIds)
                ->setMaxResults($max);

            if ($command->search) {
                $qb->leftJoin('t.executors', 'e', Join::WITH, 'e.email.value LIKE :search');
                $qb->setParameter('search', "%{$command->search}%");
            } else {
                $qb->leftJoin('t.executors', 'e');
            }

            return $qb;
        };

        $users = $this->taskRepository->getTasksUsers($tasksFilter);

        return Arr::where($users, static function (array $user) use ($command): bool {
            if (!$command->search) {
                return true;
            }

            return (bool) preg_match("/{$command->search}/", $user['email']);
        });
    }

    /**
     * @param string[] $disabledIds
     * @param mixed    $command
     *
     * @return array<int, mixed>
     */
    private function getOtherUsers($command, array $disabledIds = [], int $max = 10): array
    {
        $usersFilter = static function (QueryBuilder $qb) use ($command, $disabledIds, $max): QueryBuilder {
            $qb = $qb->setMaxResults($max);

            if (\count($disabledIds) > 0) {
                $qb = $qb
                    ->where($qb->expr()->notIn('u.uuid', ':userIds'))
                    ->setParameter('userIds', $disabledIds);
            }

            if ($command->search) {
                $qb->andWhere('u.email.value LIKE :search');
                $qb->setParameter('search', "%{$command->search}%");
            }

            return $qb;
        };

        return $this->userRepository->getUsersQuery($usersFilter);
    }
}
