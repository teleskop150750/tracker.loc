<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Bus;

use Modules\Shared\Application\Message\MessageBusInterface;
use Modules\Shared\Application\Query\QueryBusInterface;
use Modules\Shared\Application\Query\QueryInterface;
use Modules\Shared\Application\Query\QueryResponseInterface;

class QueryBus implements QueryBusInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    public function ask(QueryInterface $query): ?QueryResponseInterface
    {
        return $this->messageBus->dispatch($query);
    }
}
