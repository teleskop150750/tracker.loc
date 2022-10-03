<?php

declare(strict_types=1);

namespace Modules\Tracker\Shared\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Shared\Application\Query\GetArchiveForMe\GetArchiveForMeQuery;

class GetArchiveForMeController extends ApiController
{
    public function __invoke(EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $folders = $this->ask(GetArchiveForMeQuery::make());
            $conn->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'items' => $folders->toArray(),
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
