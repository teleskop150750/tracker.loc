<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\ResendVerificationNotification;

use Modules\Shared\Application\Command\CommandInterface;

class ResendVerificationNotificationCommand implements CommandInterface
{
    public static function make(): static
    {
        return new static();
    }
}
