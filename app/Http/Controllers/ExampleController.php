<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UrlGenerator;
use App\Support\Arr;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Auth\User\Domain\Repository\UserRepositoryInterface;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderPublished;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Domain\Services\FolderFormatter;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskPublished;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipUuid;
use Modules\Tracker\Task\Domain\Repository\FileRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRelationshipRepositoryInterface;
use Modules\Tracker\Task\Domain\Repository\TaskRepositoryInterface;
use Modules\Tracker\Task\Domain\Services\UpdateTaskStatusService;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
    }

    /**
     * @param User[] $users
     *
     * @return array<string, mixed>
     */
    public function userToArr(array $users): array
    {
        return Arr::map($users, static fn ($user) => [
            'id' => $user->getUuid()->getId(),
            'fullName' => [
                'firstName' => $user->getFullName()->getFirstName(),
                'lastName' => $user->getFullName()->getLastName(),
                'patronymic' => $user->getFullName()->getPatronymic(),
            ],
            'avatar' => $user->getAvatar()->toNative(),
            'email' => $user->getEmail()->toNative(),
            'post' => $user->getPost()->toNative(),
            'department' => $user->getDepartment()->toNative(),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function test(
        Request $request,
        EntityManagerInterface $em,
        UserRepositoryInterface $userRep,
        FolderRepositoryInterface $folderRep,
        TaskRepositoryInterface $taskRep,
        FileRepositoryInterface $fileRepository,
        TaskRelationshipRepositoryInterface $taskRelationshipRep,
        UrlGenerator $urlGenerator,
    ) {
        $task = $fileRepository->getFilesInFolders(['97b13c1e-69a6-41f9-8c63-bc3e189d8ca8']);

        dd($task);
//        dd('');
//        $dd = $taskRep->getExpiredTasks();
//        $date1 = new \DateTimeImmutable('2022-09-16');
//        $date2 = new \DateTimeImmutable('2022-09-15');
//        $diffDays = date_diff($date2, $date1)->days;
//        dd($diffDays);
//        $date = new \DateTimeImmutable();
//        $int = \DateInterval::createFromDateString('P7D');
//        dump($date->format('Y m d'));
//        DateInterval::createFromDateString()
//        $newDate = $date->modify("-1 day");
//        $res = $date->diff($newDate)->format("%r%a");
//        dump($res);
//        dd($newDate->format('Y m d'));
//        $task = $taskRep->getTaskInfo(TaskUuid::fromNative('36af42c4-0836-4240-8952-803b36abd112'));
//        $task = $taskRep->find(TaskUuid::fromNative('36af42c4-0836-4240-8952-803b36abd112'));
//        UpdateTaskStatusService::make($task)
//            ->updateStatus(
//                TaskStatus::fromNative(TaskStatus::IN_WORK)
//            );
//        dd($task);
//        dd('');
//        try {
//        $res = $frep->getFoldersFromWorkSpace(FolderUuid::fromNative('d311a908-287d-4fe7-8c44-8b53666e0406'));
//        $users = $userRep->findBy(['uuid' => ['a54beee3-91bd-4582-a074-ee4b8347a1a0']]);
//        foreach ($users as $user) {
//            $res->addSharedUser($user);
//        }
        // //
        // //        $res->addSharedUser($user);
        // //        dd($user);
//
//        dump(count($res->getSharedUsers()));
//        foreach ($res->getSharedUsers() as $user) {
//            dump($user);
//        }
        // //
//        dump(count($user->getSharedFolders()));
//        foreach ($user->getSharedFolders() as $folder) {
//            dump($folder);
//        }
//
        // //        $em->flush();
//        dd($res);

//        $left = $taskRep->find(TaskUuid::fromNative('50e42a4c-e152-4f5a-9f92-332c53c80767'));
//        $right = $taskRep->find(TaskUuid::fromNative('6aaa3748-090f-4c75-9148-73ad360d530f'));
//        $relation = new TaskRelationship(TaskRelationshipUuid::generateRandom(), $left, $right, TaskRelationshipType::fromNative(TaskRelationshipType::END_END));
//        $taskRelationshipRep->save($relation);
//        Arr::macro();
//        $par = $frep->find(FolderUuid::fromNative('edd2e075-e55c-4f5b-9e89-2d759a52e5a6'));
//        $ids = $frep->getParentFoldersEntity([$par->getUuid()]);

//        dd($relation);
//        $ids = $frep->getAvailableFoldersIdsForUser(UserUuid::fromNative('7e55360b-2c0a-4526-9d37-a75982aaa0e4'));
//        $folders = $frep->getWorkspaceFoldersForUser(UserUuid::fromNative('b222c497-8ecd-4218-94fd-d37cb02ff209'));
//        $rootWorkspace = $frep->getFoldersSharedForUser(UserUuid::fromNative('7e55360b-2c0a-4526-9d37-a75982aaa0e4'), true);
//        $rootWorkspace = $trep->getAssignedTasksIdsForUser(UserUuid::fromNative('7e55360b-2c0a-4526-9d37-a75982aaa0e4'));
//        $rootWorkspace = $trep->searchTasks('', ['bff2725b-db18-4606-bd5b-c7e1f526c4c0']);
//        $ids = $trep->getAssignedTasksForUser(UserUuid::fromNative('7e55360b-2c0a-4526-9d37-a75982aaa0e4'));
//        $ids = $trep->searchTasks('', $ids);
//        dd($ids);
//        $parent = $rootWorkspace->getParent()?->getPublisheded();
//
//        /** @var Folder[] $folders */
//        $folders = [$rootWorkspace, ...$frep->children(node: $rootWorkspace, includeTasks: true)];
//
//        foreach ($folders as $item) {
//            $item->setPublisheded(FolderPublished::fromNative(false));
//
//            foreach ($item->getTasks() as $task) {
//                $task->setPublished(TaskPublished::fromNative(false));
//                dump($task->getPublished());
//            }
//        }
//
//        $em->flush();

//        dd($ids);
//        $rootWorkspace = $folderRep->children($rootWorkspace);
//        $rootWorkspace = FolderFormatter::makeFromArray($rootWorkspace)->arrToTree()->getFolders();
//        $rootWorkspace1 = $frep->findOrNull(FolderUuid::fromNative('d870bb67-6caf-41f7-bb97-137cd040a4bd'));
//        $rootWorkspace2 = $frep->findOrNull(FolderUuid::fromNative('71d85096-e55b-4ade-b574-353aac9ff90b'));
//        $rootWorkspace2 = $frep->findOrNull(FolderUuid::fromNative('16855b64-25ec-4980-ac9b-bd0c40c3e48a'));
//        dd($rootWorkspace1);
//        $rootWorkspace = $frep->children(node: $rootWorkspace, includeNode:  true);
        // //        $rootWorkspace = $frep->childrenForArr(node: [$rootWorkspace1, $rootWorkspace2]);
//        $rootWorkspace = FolderFormatter::make($rootWorkspace)
//            ->foldersToArr()
//            ->arrToTree()
//            ->formatTree()
//            ->treeToItems()
//            ->getFolders()
//        ;
//        dd($rootWorkspace);
//        $rootWorkspace = FolderFormatter::make()->arrToTree($rootWorkspace);

//        return array_map(static fn($folder) => [
//            'id' => $folder->getUuid()->getId(),
//            'name' => $folder->getName()->toNative(),
//            'access' => $folder->getAccess()->toNative(),
//            'type' => $folder->getType()->toNative(),
//            'sharedUsers' => $this->userToArr($folder->getSharedUsers()->toArray()),
//            'parentId' => $folder->getParent()
//                ? $folder->getParent()->getUuid()->getId()
//                : null,
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
//        ], $rootWorkspace);

//        dd($data);
//            echo '<pre>';
//            var_export($request->all());
//            echo '</pre>';

        return new JsonResponse($request->all());
//            $res = ['id' => 3];
//            $userRepository = App::make(UserRepositoryInterface::class);
//            $user = $userRepository->find(new UserUuid('30853c06-66e7-4645-bb4b-9ae8b0bc1aec'));
        // //            $user = $em->getRepository(User::class)->findBy(['uuid' => '30853c06-66e7-4645-bb4b-9ae8b0bc1aec']);
//            dd($user);
//
//            $queryBuilder = $em->createQueryBuilder();
//            $result = $queryBuilder->select('f', 'a', 'su')
//                ->from(Folder::class, 'f')
//                ->join('f.author', 'a')
//                ->leftJoin('f.sharedUsers', 'su')
//                ->where('a.uuid = :author_id')
//                ->andWhere('f.type.value = :type')
//                ->setParameter('type', FolderType::ROOT)
//                ->setParameter('author_id', '30853c06-66e7-4645-bb4b-9ae8b0bc1aec')
//                ->getQuery()
//                ->getOneOrNullResult();
//            $result = $frep->children(node: $result, includeNode: true);
//
//            $result = array_map(static fn ($folder) => [
//                'id' => $folder->getUuid()->getId(),
//                'name' => $folder->getName()->toNative(),
//                'access' => $folder->getAccess()->toNative(),
//                'type' => $folder->getType()->toNative(),
//                'parentId' => $folder->getParent()
//                    ? $folder->getParent()->getUuid()->getId()
//                    : null,
//                'author' => [
//                    'id' => $folder->getAuthor()->getUuid()->getId(),
//                    'fullName' => [
//                        'firstName' => $folder->getAuthor()->getFullName()->getFirstName(),
//                        'lastName' => $folder->getAuthor()->getFullName()->getLastName(),
//                        'patronymic' => $folder->getAuthor()->getFullName()->getPatronymic(),
//                    ],
//                    'avatar' => $folder->getAuthor()->getAvatar()->toNative(),
//                    'email' => $folder->getAuthor()->getEmail()->toNative(),
//                ],
//            ], $result);
//            echo '<pre>';
        // //            var_export($result);
//            echo '</pre>';
//            dd($result);

//            $res = $em->getRepository(User::class)->findAll();
//            dd($res);

//        dd($error->errors());

//        $res = $em->getRepository(Category::class)->findAll();
//        dd($res);

//            $folder1 = new Category();
//            $folder1->setTitle('2');
//            $folder2 = new Category();
//            $folder2->setTitle('2');
//            $folder3 = new Category();
//            $folder3->setTitle('3');
//            $folder3->setParent($folder1);
//            $folder4 = new Category();
//            $folder4->setTitle('4');
//            $folder4->setParent($folder1);
//            $em->persist($folder1);
//            $em->persist($folder2);
//            $em->persist($folder3);
//            $em->persist($folder4);
        // //            $em->flush();
//            $res = $em->getRepository(Category::class)->findAll();
//            dd($res);

//        $a1 = new Test();
//        $a1->setName('23');
//        $a2 = new Test();
//        $a2->setName('23');
//        $em->persist($a1);
//        $em->persist($a2);
//        $em->flush();
//        $res = $em->getRepository(Test::class)->findAll();
//        dd($res);
//            return new JsonResponse($res);
//        } catch (ValidationException $exception) {
//            dd($exception->validator->errors()->all());
//
//            return $exception->getResponse();
//
//            return new JsonResponse(['error' => true]);
//        } catch (NonUniqueResultException $e) {
//        }
    }
}
