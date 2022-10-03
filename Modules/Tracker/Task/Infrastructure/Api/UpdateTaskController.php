<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Task\Application\Command\UpdateTask\UpdateTaskCommand;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateTaskController extends ApiController
{
    public function __invoke(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $conn = $em->getConnection();
        $conn->beginTransaction();

        try {
            $taskData = $this->validate(
                $request->all(),
                [
                    'id' => ['required'],
                    'name' => ['nullable'],
                    'folder' => ['nullable'],
                    'status' => ['nullable'],
                    'importance' => ['nullable'],
                    'startDate' => ['nullable'],
                    'endDate' => ['nullable'],
                    'description' => ['nullable'],
                    'executors' => ['nullable', 'array'],
                    'relationships' => ['nullable', 'array'],
                    'published' => ['nullable'],
                ]
            );

            $this->dispatch(UpdateTaskCommand::createFromArray($taskData));
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
}
