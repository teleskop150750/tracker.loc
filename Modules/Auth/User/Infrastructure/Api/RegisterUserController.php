<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Modules\Auth\User\Application\Command\RegisterUser\RegisterUserCommand;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegisterUserController extends ApiController
{
    public function register(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $userData = $this->validate(
                $request->all(),
                [
                    'userId' => ['required', 'uuid'],
                    'firstName' => ['required'],
                    'lastName' => ['required'],
                    'patronymic' => ['nullable'],
                    'email' => ['required', 'email'],
                    'phone' => ['nullable'],
                    'post' => ['nullable'],
                    'department' => ['nullable'],
                    'password' => ['required', 'min:8'],
                    'passwordConfirm' => ['required', 'same:password'],
                ]
            );

            $this->dispatch(RegisterUserCommand::createFromArray($userData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Успешный регистрация',
                'data' => $userData,
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }
}
