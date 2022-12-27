<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Task\Application\Command\TaskAddFile\TaskAddFileCommand;
use Modules\Tracker\Task\Application\Command\TaskRemoveFile\TaskRemoveFileCommand;
use Modules\Tracker\Task\Application\Query\DownloadFile\DownloadFileQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TaskFileController extends ApiController
{
    public function add(Request $request, EntityManagerInterface $em, string $taskId): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $this->validate(
                $request->all(),
                [
                    'file' => ['required'],
                ]
            );

            $fileId = (new \Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid())->generateRandom()->getId();
            $data = [
                'file' => $request->file('file'),
                'taskId' => $taskId,
                'fileId' => $fileId,
            ];

            $this->dispatch(TaskAddFileCommand::createFromArray($data));
            $conn->commit();

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'id' => $fileId,
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

    public function remove(Request $request, EntityManagerInterface $em, string $fileId): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $data = ['fileId' => $fileId];

            $this->dispatch(TaskRemoveFileCommand::createFromArray($data));
            $conn->commit();

            return new JsonResponse([
                'success' => true,
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

    public function download(string $fileId): StreamedResponse|JsonResponse
    {
        try {
            $data = ['fileId' => $fileId];
            $file = $this->ask(DownloadFileQuery::createFromArray($data));
            $fileMap = $file->toArray();

            return Storage::download($fileMap['path']);
        } catch (ValidationException $exception) {
            return ValidationExceptionNormalizer::make($exception)->getResponse();
        } catch (\Exception $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
