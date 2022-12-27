<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Modules\Auth\User\Application\Command\ResendVerificationNotification\ResendVerificationNotificationCommand;
use Modules\Auth\User\Application\Command\VerifyEmail\VerifyEmailCommand;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;

class VerificationController extends ApiController
{
    public function resendVerificationNotification(): JsonResponse
    {
        try {
            $this->dispatch(ResendVerificationNotificationCommand::make());

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Письмо отправлено',
            ]);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function verifyEmail(EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $this->dispatch(VerifyEmailCommand::make());
            $conn->commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'title' => 'Почта подтверждена',
            ]);
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
        }
    }
}
