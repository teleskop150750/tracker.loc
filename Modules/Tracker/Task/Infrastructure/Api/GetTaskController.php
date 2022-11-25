<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Task\Application\Query\GetTask\GetTaskQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetTaskController extends ApiController
{
    public function __invoke(Request $request, EntityManagerInterface $em, string $id): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $folder = $this->ask(GetTaskQuery::createFromArray(['id' => $id]));
            $conn->commit();

            return new JsonResponse([
                'success' => true,
                'data' => $folder->toArray(),
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
