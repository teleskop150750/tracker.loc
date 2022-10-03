<?php

declare(strict_types=1);

namespace Modules\Auth\User\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Shared\Infrastructure\Doctrine\Types\UuidValueObjectType;

final class UserUuidType extends UuidValueObjectType
{
    protected const TYPE = 'user_uuid';

    /**
     * @param null|string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserUuid
    {
        if (null === $value) {
            return null;
        }

        return UserUuid::fromNative($value);
    }
}
