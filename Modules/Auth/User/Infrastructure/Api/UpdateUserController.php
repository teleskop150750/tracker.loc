<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Application\Command\UpdateUser\UpdateUserCommand;
use Modules\Auth\User\Application\Command\UpdateUserPassword\UpdateUserPasswordCommand;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
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

            return new JsonResponse([
                'success' => true,
                'data' => $userData,
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

            return new JsonResponse([
                'success' => true,
                'data' => $userData,
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
