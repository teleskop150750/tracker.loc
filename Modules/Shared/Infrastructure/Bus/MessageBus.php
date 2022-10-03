<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Bus;

use Modules\Shared\Application\Message\MessageBusInterface;

class MessageBus implements MessageBusInterface
{
    public function __construct(
        private array $handlers
    ) {
    }

    public function dispatch(object $query): mixed
    {
        $handle = $this->handlers[$query::class][0];

        return $handle($query);
    }
}
