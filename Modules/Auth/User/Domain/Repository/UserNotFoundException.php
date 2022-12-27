<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Repository;

use App\Exceptions\HttpException;

class UserNotFoundException extends HttpException
{
}
