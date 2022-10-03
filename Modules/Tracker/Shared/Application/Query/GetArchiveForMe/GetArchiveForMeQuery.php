<?php

declare(strict_types=1);

namespace Modules\Tracker\Shared\Application\Query\GetArchiveForMe;

use Modules\Shared\Application\Query\QueryInterface;

class GetArchiveForMeQuery implements QueryInterface
{
    public function __construct()
    {
    }

    public static function make(): static
    {
        return new static();
    }
}
