<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Task\Application\Query\GetTasksCreatedByMe\GetTasksCreatedByMeQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetTasksCreatedByMeController extends ApiController
{
    public function __invoke(EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $folders = $this->ask(GetTasksCreatedByMeQuery::make());
            $conn->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'tasks' => $folders->toArray(),
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
