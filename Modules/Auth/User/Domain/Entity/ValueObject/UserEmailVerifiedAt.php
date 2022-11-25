<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\ValueObject;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\DateTime\NullableDateTime;

#[Embeddable]
class UserEmailVerifiedAt extends NullableDateTime
{
    #[Column(name: 'email_verified_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $value;
}
