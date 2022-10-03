<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Query\GetAllUsers;

use Modules\Shared\Application\Query\QueryInterface;

class GetALlUsersQuery implements QueryInterface
{
    public function __construct()
    {
    }

    public static function make(): static
    {
        return new static();
    }
}
