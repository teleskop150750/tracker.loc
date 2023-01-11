<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Task\Application\Command\TaskAddFile\TaskAddFileCommand;
use Modules\Tracker\Task\Application\Command\TaskRemoveFile\TaskRemoveFileCommand;
use Modules\Tracker\Task\Application\Query\DownloadFile\DownloadFileQuery;
use Symfony\Component\HttpFoundation\JsonResponse;

class TaskFileController extends ApiController
{
    public function create(Request $request, EntityManagerInterface $em, string $taskId): Response
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $data = $this->validate(
                [...$request->all(), 'taskId' => $taskId],
                [
                    'taskId' => ['required', 'uuid'],
                    'file' => ['required', 'file'],
                ]
            );

            // $file = $request->file('file');
            // $fileName = $file->getClientOriginalName();
            // $response = Http::withToken(env('FILE_STORAGE_TOKEN'))
            //     ->attach('attachment', file_get_contents($file->path()), $fileName)
            //     ->post(env('FILE_STORAGE_API') . '/api/v1/files', $request->all());

            // if ($response->status() === 201) {
            //     $fileId = $request->json('data.id');
            //     $this->dispatch(TaskAddFileCommand::createFromArray(['id' => $fileId]));
            //     $conn->commit();
            // }

            /** @var \Modules\Tracker\Task\Application\Command\TaskAddFile\TaskAddFileResponse $taskAddFileResponse */
            $taskAddFileResponse = $this->dispatch(TaskAddFileCommand::createFromArray($data));
            $conn->commit();
            $resposnse = $taskAddFileResponse->getReponse();

            return response($resposnse->body(), $resposnse->status(), $resposnse->headers());
        } catch (\Exception $exception) {
            $conn->rollBack();

            throw $exception;
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

    public function download(string $fileId): Response
    {
        $data = ['fileId' => $fileId];
        $file = $this->ask(DownloadFileQuery::createFromArray($data));
        $arr = $file->toArray();

        return response($arr['body'], $arr['status'], $arr['headers']);
    }
}