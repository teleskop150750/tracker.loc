<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Application\Query\GetAllUsers\GetALlUsersQuery;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetAllUsersController extends ApiController
{
    public function __invoke(EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $users = $this->ask(GetALlUsersQuery::make());
            $conn->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'users' => $users->toArray(),
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
