<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetFolder;

use Modules\Shared\Application\Query\QueryResponseInterface;

class GetFolderResponse implements QueryResponseInterface
{
    /**
     * @param array<string, mixed> $folder
     */
    public function __construct(
        private readonly array $folder
    ) {
    }

    /**
     * @param array<string, mixed> $folder
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
