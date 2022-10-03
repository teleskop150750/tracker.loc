<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Folder\Application\Query\GetFolder\GetFolderQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetFolderController extends ApiController
{
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $folderData = $this->validate(
                $request->all(),
                [
                    'id' => ['required'],
                ]
            );

            $folder = $this->ask(GetFolderQuery::createFromArray($folderData));
            $conn->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'folder' => $folder->toArray(),
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
