<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\SearchFolders;

use Modules\Shared\Application\Query\QueryResponseInterface;

class SearchFoldersQueryResponse implements QueryResponseInterface
{
    /**
     * @param array<int, mixed> $folder
     */
    public function __construct(
        private readonly array $folder
    ) {
    }

    /**
     * @param array<int, mixed> $folder
     */
    public static function fromArray(array $folder): static
    {
        return new static($folder);
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->folder;
    }
}
