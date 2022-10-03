<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Query;

interface QueryResponseInterface
{
    public function toArray(): array;
}
