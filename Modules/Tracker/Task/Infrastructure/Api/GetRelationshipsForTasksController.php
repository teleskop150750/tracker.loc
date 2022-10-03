<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Task\Application\Query\GetRelationshipsForTasks\GetRelationshipsForTasksQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetRelationshipsForTasksController extends ApiController
{
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $data = $this->validate(
                $request->all(),
                [
                    'taskIds' => ['nullable', 'array'],
                ]
            );
            $tasks = $this->ask(GetRelationshipsForTasksQuery::createFromArray($data));
            $conn->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'relationships' => $tasks->toArray(),
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
