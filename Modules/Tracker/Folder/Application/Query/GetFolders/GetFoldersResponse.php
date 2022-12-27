<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetFolders;

use Modules\Shared\Application\Query\QueryResponseInterface;

class GetFoldersResponse implements QueryResponseInterface
{
    /**
     * @var array<int, mixed>
     */
    private readonly array $folders;

    /**
     * @param array<int, mixed> $folders
     */
    public function __construct(array $folders = [])
    {
        $this->folders = $folders;
    }

    /**
     * @param array<int, mixed> $folders
     */
    public static function fromArray(array $folders): static
    {
        return new static($folders);
    }

    /**
     * @return array{meta: array<string, mixed>, data: array<int, mixed>}
     */
    public function toArray(): array
    {
        return [
            'meta' => [
                'total' => \count($this->folders),
            ],
            'data' => $this->folders,
        ];
    }
}
