<?php

declare(strict_types=1);

namespace Modules\Auth\User\Application\Command\VerifyEmail;

use Modules\Shared\Application\Command\CommandInterface;

class VerifyEmailCommand implements CommandInterface
{
    public static function make(): static
    {
        return new static();
    }
}
