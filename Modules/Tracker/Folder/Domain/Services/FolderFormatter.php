<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Services;

use App\Support\Arr;
use Modules\Auth\User\Domain\Services\UserFormatter;
use Modules\Shared\Domain\ValueObject\DateTime\DateTime;
use Modules\Tracker\Task\Domain\Services\TaskFormatter;

class FolderFormatter
{
    /**
     * @var array<int, array{
     *     id: string,
     *     level: int,
     *     createdAt: \DateTimeImmutable,
     *     updatedAt: \DateTimeImmutable,
     *     name: string,
     *     type: string,
     *     published: bool,
     *  }>
     */
    private array $folders;

    /**
     * @param array<int, array{
     *     id: string,
     *     level: int,
     *     createdAt: \DateTimeImmutable,
     *     updatedAt: \DateTimeImmutable,
     *     name: string,
     *     type: string,
     *     published: bool,
     *  }> $folders
     */
    public function __construct(array $folders)
    {
        $this->folders = $folders;
    }

    /**
     * @param array<int, array{
     *     id: string,
     *     level: int,
     *     createdAt: \DateTimeImmutable,
     *     updatedAt: \DateTimeImmutable,
     *     name: string,
     *     type: string,
     *     published: bool,
     *  }> $folders
     */
    public static function makeFromArray(array $folders = []): static
    {
        return new static($folders);
    }

    public function getFolders(): array
    {
        return $this->folders;
    }

//    public function foldersToArr(): static
//    {
//        $data = [];
//
//        foreach ($this->folders as $folder) {
//            $data[] = $this->folderToArr($folder);
//        }
//
//        $this->folders = $data;
//
//        return $this;
//    }

//    /**
//     * @return array<string, mixed>
//     */
//    public function folderToArr(Folder $folder): array
//    {
//        return [
//            'id' => $folder->getUuid()->getId(),
//            'name' => $folder->getName()->toNative(),
//            'access' => $folder->getAccess()->toNative(),
//            'type' => $folder->getType()->toNative(),
//            'sharedUsers' => $this->usersToArr($folder->getSharedUsers()->toArray()),
//            'parentId' => $folder->getParent()
//                ? $folder->getParent()->getUuid()->getId()
//                : null,
//            'createdAt' => $folder->getCreatedAt()->format('Y-m-d H:i:s'),
//            'author' => [
//                'id' => $folder->getAuthor()->getUuid()->getId(),
//                'fullName' => [
//                    'firstName' => $folder->getAuthor()->getFullName()->getFirstName(),
//                    'lastName' => $folder->getAuthor()->getFullName()->getLastName(),
//                    'patronymic' => $folder->getAuthor()->getFullName()->getPatronymic(),
//                ],
//                'avatar' => $folder->getAuthor()->getAvatar()->toNative(),
//                'email' => $folder->getAuthor()->getEmail()->toNative(),
//            ],
//            'entityType' => 'FOLDER',
//        ];
//    }

//    /**
//     * @param User[] $users
//     *
//     * @return array<int, mixed>
//     */
//    public function usersToArr(array $users): array
//    {
//        $result = [];
//
//        foreach ($users as $user) {
//            $result[] = $this->userToArr($user);
//        }
//
//        return $result;
//    }

//    /**
//     * @return array<string, mixed>
//     */
//    public function userToArr(User $user): array
//    {
//        return [
//            'id' => $user->getUuid()->getId(),
//            'fullName' => [
//                'firstName' => $user->getFullName()->getFirstName(),
//                'lastName' => $user->getFullName()->getLastName(),
//                'patronymic' => $user->getFullName()->getPatronymic(),
//            ],
//            'avatar' => $user->getAvatar()->toNative(),
//            'email' => $user->getEmail()->toNative(),
//            'post' => $user->getPost()->toNative(),
//            'department' => $user->getDepartment()->toNative(),
//        ];
//    }

    public function listToTree(): static
    {
        $hashTable = [];
        $hashIds = [];
        $tree = [];

        foreach ($this->folders as $item) {
            if (!isset($item['parentId']) && isset($item['parent'])) {
                $item['parentId'] = $item['parent'] ? $item['parent']['id'] : null;
                unset($item['parent']);
            }

            if (!isset($item['parentId'])) {
                $item['parentId'] = null;
            }

            $hashTable[$item['id']] = $item;
            $hashIds[$item['id']] = $item['id'];
            $hashTable[$item['id']]['children'] = [];
        }

        foreach ($hashTable as &$hashItem) {
            $parentId = $hashItem['parentId'];

            if (!empty($hashIds[$parentId])) {
                $hashTable[$parentId]['children'][] = &$hashItem;
            } else {
                $hashItem['parentId'] = null;
                $tree[] = &$hashItem;
            }
        }

        unset($hashItem);

        $this->folders = $tree;

        return $this;
    }

    /**
     * @param array<int, mixed> $initPath
     *
     * @return $this
     */
    public function formatTree(array $initPath = []): static
    {
        $this->folders = $this->formatTreeProcess($this->folders, $initPath);

        return $this;
    }

    /**
     * @param array<int, string> $init
     *
     * @return $this
     */
    public function generatePath(array $init = []): static
    {
        $this->folders = $this->generatePathProcess($this->folders, $init);

        return $this;
    }

    public function treeToList(): static
    {
        $this->folders = $this->treeToListProcess($this->folders);

        return $this;
    }

    public function formatDQLFolders(): static
    {
        $result = [];

        foreach ($this->folders as $folder) {
            $result[] = $this->formatDQLFolder($folder);
        }

        $this->folders = Arr::keyBy($result, 'id');

        return $this;
    }

    public function flatTasksInFolders(): static
    {
        $result = [];
        foreach ($this->folders as $folder) {
            $result[] = $folder;

            if (isset($folder['tasks'])) {
                foreach ($folder['tasks'] as $task) {
                    $result[] = $task;
                }

                unset($folder['tasks']);
            }
        }

        $this->folders = $result;

        return $this;
    }

    /**
     * @param array<int, mixed> $folder
     *
     * @return array<int, mixed>
     */
    public function formatDQLFolder(array $folder): array
    {
        $key = isset($folder['0']['descendant']) ? 'descendant' : 'ancestor';
        $folderData = $folder['0'][$key];

        $result = [
            'id' => $folderData['id'],
            'parentId' => $folder['parentId'],
            'name' => $folderData['name.value'],
            'access' => $folderData['access.value'],
            'type' => $folderData['type.value'],
            'level' => $folderData['level'],
            'publish' => $folderData['published.value'],
            'author' => UserFormatter::make()->formatDQLUser($folderData['author']),
            'sharedUsers' => UserFormatter::make()->formatDQLUsers($folderData['sharedUsers']),
            'createdAt' => $folderData['createdAt']->format(DateTime::W3C),
            'entityType' => 'FOLDER',
        ];

        if (isset($folderData['tasks'])) {
            $result['tasks'] = TaskFormatter::make()->formatDqlTasks($folderData['tasks'], $folderData['id']);
        }

        return $result;
    }

    /**
     * @param array<int, mixed>  $items
     * @param array<int, string> $path
     *
     * @return array<int, mixed>
     */
    private function formatTreeProcess(array &$items, array $path = []): array
    {
        foreach ($items as &$item) {
            $item['path'] = $path;
            $accPath = [...$path, $item['name']];

            if (\count($item['children']) > 0) {
                $item['children'] = $this->formatTreeProcess($item['children'], $accPath);
            }
        }

        return $items;
    }

    /**
     * @param array<int, mixed>  $items
     * @param array<int, string> $path
     *
     * @return array<int, string>
     */
    private function generatePathProcess(array &$items, array $path = []): array
    {
        foreach ($items as &$item) {
            $item['path'] = $path;
            $accPath = [...$path, $item['name']];

            if (\count($item['children']) > 0) {
                $item['children'] = $this->generatePathProcess($item['children'], $accPath);
            }
        }

        return $items;
    }

    /**
     * @param array<int, mixed> $tree
     *
     * @return array<int, mixed>
     */
    private function treeToListProcess(array $tree): array
    {
        $result = [];

        foreach ($tree as $item) {
            if (\count($item['children']) > 0) {
                $result = [...$result, ...$this->treeToListProcess($item['children'])];
            }

            unset($item['children']);

            $result[] = $item;
        }

        return $result;
    }
}
