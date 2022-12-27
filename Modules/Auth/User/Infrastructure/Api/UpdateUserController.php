<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Modules\Auth\User\Application\Command\UpdateUser\UpdateUserCommand;
use Modules\Auth\User\Application\Command\UpdateUserPassword\UpdateUserPasswordCommand;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateUserController extends ApiController
{
    public function updateInfo(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $userData = $this->validate(
                $request->all(),
                [
                    'firstName' => ['required'],
                    'lastName' => ['required'],
                    'patronymic' => ['nullable'],
                    'phone' => ['nullable'],
                    'post' => ['nullable'],
                    'department' => ['nullable'],
                ]
            );

            $this->dispatch(UpdateUserCommand::createFromArray($userData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Обновлено',
                'data' => $userData,
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }

    public function updatePassword(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $userData = $this->validate(
                $request->all(),
                [
                    'currentPassword' => ['required'],
                    'password' => ['required'],
                    'passwordConfirm' => ['required', 'same:password'],
                ]
            );

            $this->dispatch(UpdateUserPasswordCommand::createFromArray($userData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Обновлено',
                'data' => $userData,
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }
}
