<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Application\Command\RegisterUser\RegisterUserCommand;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Folder\Application\Command\CreateFolder\CreateFolderCommand;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderAccess;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderName;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Symfony\Component\HttpFoundation\JsonResponse;

class RegisterUserController extends ApiController
{
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $userData = $this->validate(
                $request->all(),
                [
                    'userId' => ['required', 'uuid'],
                    'folderId' => ['required', 'uuid'],
                    'firstName' => ['required'],
                    'lastName' => ['required'],
                    'patronymic' => ['required'],
                    'email' => ['required', 'email'],
                    'phone' => ['nullable'],
                    'post' => ['nullable'],
                    'department' => ['nullable'],
                    'password' => ['required'],
                    'passwordConfirm' => ['required', 'same:password'],
                ]
            );

            $this->dispatch(RegisterUserCommand::createFromArray($userData));

            $folderData = [
                'folderId' => $userData['folderId'],
                'author' => $userData['userId'],
                'name' => FolderName::DEFAULT,
                'access' => FolderAccess::PRIVATE,
                'type' => FolderType::ROOT,
            ];

            $this->dispatch(CreateFolderCommand::createFromArray($folderData));
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