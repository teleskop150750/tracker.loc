<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetSharedFoldersForMe;

use Modules\Shared\Application\Query\QueryInterface;

class GetSharedFoldersForMeQuery implements QueryInterface
{
    public function __construct()
    {
    }

    public static function make(): static
    {
        return new static();
    }
}
