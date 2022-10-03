<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\SearchFolders;

use Modules\Shared\Application\Query\QueryInterface;

class SearchFoldersQuery implements QueryInterface
{
    public function __construct(
        readonly string $search
    ) {
    }

    public static function createFromArray(array $data): static
    {
        return new static(
            $data['search'] ?? '',
        );
    }
}
