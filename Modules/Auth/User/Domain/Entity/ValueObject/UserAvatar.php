<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\NullableStringLiteral;

#[Embeddable]
class UserAvatar extends NullableStringLiteral
{
    #[Column(name: 'avatar', type: 'string', nullable: true)]
    protected null|string $value;

    public static function fromNative(string $value = null): self
    {
        return new self($value);
    }
}
