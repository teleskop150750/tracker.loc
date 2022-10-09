<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\NullableStringLiteral;

#[Embeddable]
class UserPhone extends NullableStringLiteral
{
    #[Column(name: 'phone', type: 'string', nullable: true)]
    protected null|string $value;
}