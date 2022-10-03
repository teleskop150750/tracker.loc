<?php

declare(strict_types=1);

namespace Modules\Tracker\Shared\Application\Query\GetArchiveForMe;

use Modules\Shared\Application\Query\QueryResponseInterface;

class GetArchiveForMeResponse implements QueryResponseInterface
{
    /**
     * @param array<int, mixed> $items
     */
    public function __construct(
        private readonly array $items
    ) {
    }

    /**
     * @param array<int, mixed> $items
     */
    public static function fromArray(array $items): static
    {
        return new static($items);
    }

    /**
     * @return array<int, mixed>
     */
    public function toArray(): array
    {
        return $this->items;
    }
}
