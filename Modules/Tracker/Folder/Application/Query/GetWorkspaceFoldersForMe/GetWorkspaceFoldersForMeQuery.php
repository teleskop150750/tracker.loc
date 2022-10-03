<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetWorkspaceFoldersForMe;

use Modules\Shared\Application\Query\QueryInterface;

class GetWorkspaceFoldersForMeQuery implements QueryInterface
{
    public function __construct()
    {
    }

    public static function make(): static
    {
        return new static();
    }
}
