<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Modules\Auth\User\Application\Command\ForgotPassword\ForgotPasswordCommand;
use Modules\Auth\User\Application\Command\ResetPassword\ResetPasswordCommand;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class PasswordController extends ApiController
{
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $userData = $this->validate(
                $request->all(),
                [
                    'email' => ['required', 'email'],
                ]
            );

            $this->dispatch(ForgotPasswordCommand::createFromArray($userData));

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Письмо отправлено',
            ]);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function resetPassword(Request $request, EntityManagerInterface $em, string $token): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $userData = $this->validate(
                [...$request->all(), 'token' => $token],
                [
                    'token' => ['required', 'uuid'],
                    'email' => ['required', 'email'],
                    'password' => ['required', 'min:8'],
                    'passwordConfirm' => ['required', 'same:password'],
                ]
            );

            $this->dispatch(ResetPasswordCommand::createFromArray($userData));
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Success',
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }
}
