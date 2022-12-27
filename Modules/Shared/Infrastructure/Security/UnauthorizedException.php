<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Security;

use App\Exceptions\HttpException;

class UnauthorizedException extends HttpException
{
}
