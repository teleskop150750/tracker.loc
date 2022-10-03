<?php

declare(strict_types=1);

namespace Modules\Tracker\Shared\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Folder\Application\Query\SearchFolders\SearchFoldersQuery;
use Modules\Tracker\Task\Application\Query\SearchTasks\SearchTasksQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchController extends ApiController
{
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $folderData = $this->validate(
                $request->all(),
                [
                    'search' => ['nullable'],
                ]
            );

            $folders = $this->ask(SearchFoldersQuery::createFromArray($folderData));
            $tasks = $this->ask(SearchTasksQuery::createFromArray($folderData));
            $conn->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'folders' => $folders->toArray(),
                    'tasks' => $tasks->toArray(),
                ],
            ]);
        } catch (ValidationException $exception) {
            $conn->rollBack();

            return ValidationExceptionNormalizer::make($exception)->getResponse();
        } catch (\Exception $exception) {
            $conn->rollBack();

            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
