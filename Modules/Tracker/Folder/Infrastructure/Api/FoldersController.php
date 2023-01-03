<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Infrastructure\Api;

use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Tracker\Folder\Application\Command\CreateFolder\CreateFolderCommand;
use Modules\Tracker\Folder\Application\Command\DeleteFolder\DeleteFolderCommand;
use Modules\Tracker\Folder\Application\Command\UpdateFolder\UpdateFolderCommand;
use Modules\Tracker\Folder\Application\Query\GetFolder\GetFolderQuery;
use Modules\Tracker\Folder\Application\Query\GetFolders\GetFoldersQuery;

class FoldersController extends ApiController
{
    public function index(): JsonResponse
    {
        $folders = $this->ask(GetFoldersQuery::make())->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Folders',
            'meta' => $folders['meta'],
            'data' => $folders['data'],
        ]);
    }

    /**
     * @throws ConnectionException
     */
    public function create(Request $request, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $folderData = $this->validate(
                $request->all(),
                [
                    'id' => ['required', 'uuid'],
                    'name' => ['required'],
                    'parent' => ['required'],
                    'sharedUsers' => ['nullable', 'array'],
                ]
            );

            $this->dispatch(CreateFolderCommand::createFromArray($folderData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Папка создана',
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }

    public function show(string $id): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $folderData = $this->validate(['id' => $id], ['id' => ['required', 'uuid']]);

        $folder = $this->ask(GetFolderQuery::createFromArray($folderData));

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Папка',
            'data' => $folder->toArray(),
        ]);
    }

    public function update(Request $request, EntityManagerInterface $em, string $id): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $folderData = $this->validate(
                [...$request->all(), 'id' => $id],
                [
                    'id' => ['required', 'uuid'],
                    'name' => ['nullable'],
                    'sharedUsers' => ['nullable'],
                ]
            );

            $this->dispatch(UpdateFolderCommand::createFromArray($folderData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Папка обновлена',
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }

    /**
     * @throws ConnectionException
     */
    public function delete(EntityManagerInterface $em, string $id): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $folderData = $this->validate(
                ['id' => $id],
                ['id' => ['required', 'uuid']]
            );

            $this->dispatch(DeleteFolderCommand::createFromArray($folderData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Папка удалена',
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }
}
