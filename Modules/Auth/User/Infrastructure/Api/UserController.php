<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Illuminate\Http\Request;
use Modules\Auth\User\Application\Query\GetUsers\GetUsersQuery;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->get('search', '');
        $users = $this->ask(GetUsersQuery::createFromArray(['search' => $search]))->toArray();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Users',
            'meta' => $users['meta'],
            'data' => $users['data'],
        ]);
    }
}
