<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Repository\Exceptions;

use App\Exceptions\HttpException;

class TaskNotFoundException extends HttpException
{
}
