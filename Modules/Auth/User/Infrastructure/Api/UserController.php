<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Application\Query\GetUsers\GetUsersQuery;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends ApiController
{
    public function index(EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $users = $this->ask(GetUsersQuery::make())->toArray();
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Users',
                'meta' => $users['meta'],
                'data' => $users['data'],
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }
}
