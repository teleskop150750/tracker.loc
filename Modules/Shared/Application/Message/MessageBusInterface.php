<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Message;

interface MessageBusInterface
{
    public function dispatch(object $message): mixed;
}
