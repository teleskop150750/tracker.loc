<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Api;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Shared\Infrastructure\Lumen\ApiController;
use Modules\Shared\Infrastructure\Lumen\ValidationExceptionNormalizer;
use Modules\Tracker\Task\Application\Command\DeleteTask\DeleteTaskCommand;
use Symfony\Component\HttpFoundation\JsonResponse;

class DeleteTaskController extends ApiController
{
}
