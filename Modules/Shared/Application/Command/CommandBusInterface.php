<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Command;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): ?CommandResponseInterface;
}
