<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Query;

interface QueryBusInterface
{
    public function ask(QueryInterface $query): ?QueryResponseInterface;
}
