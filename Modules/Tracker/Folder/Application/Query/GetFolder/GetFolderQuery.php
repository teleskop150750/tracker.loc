<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetFolder;

use Modules\Shared\Application\Query\QueryInterface;

class GetFolderQuery implements QueryInterface
{
    public function __construct(readonly string $id)
    {
    }

    public static function createFromArray(array $data): static
    {
        return new static($data['id'],);
    }
}
