<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Application\Query\GetAvailableFoldersForMe;

use Modules\Shared\Application\Query\QueryResponseInterface;

class GetAvailableFoldersForMeResponse implements QueryResponseInterface
{
    /**
     * @param array<int, mixed> $folders
     */
    public function __construct(
        private readonly array $folders
    ) {
    }

    /**
     * @param array<int, mixed> $folders
     */
    public static function fromArray(array $folders): static
    {
        return new static($folders);
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->folders;
    }
}
