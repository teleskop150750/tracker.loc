<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Bus;

use Modules\Shared\Application\Command\CommandBusInterface;
use Modules\Shared\Application\Command\CommandInterface;
use Modules\Shared\Application\Command\CommandResponseInterface;
use Modules\Shared\Application\Message\MessageBusInterface;

class CommandBus implements CommandBusInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->messageBus = $commandBus;
    }

    public function dispatch(CommandInterface $command): ?CommandResponseInterface
    {
        return $this->messageBus->dispatch($command);
    }
}
