<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\Identity\Token;

#[Embeddable]
class PasswordToken extends Token
{
    #[Column(name: 'token', type: 'string')]
    protected string $value;
}
