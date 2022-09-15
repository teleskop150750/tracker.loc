<?php

namespace App\Modules\Shared\Application\Command;

interface CommandHandlerInterface
{
    public function execute(CommandInterface $command): mixed;
}
