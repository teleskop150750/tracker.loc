<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Modules\Auth\User\Application\Query\LoginUser\LoginUserQuery;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class LoginUserController extends ApiController
{
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = $this->validate(
            $request->all(),
            [
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]
        );

        $user = $this->ask(LoginUserQuery::createFromArray($data));

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'title' => 'Успешный выход',
            'data' => $user->toArray(),
        ]);
    }
}
