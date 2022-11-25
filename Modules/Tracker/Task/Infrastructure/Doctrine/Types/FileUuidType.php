<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Shared\Infrastructure\Doctrine\Types\UuidValueObjectType;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;

class FileUuidType extends UuidValueObjectType
{
    protected const TYPE = 'file_uuid';

    /**
     * @param null|string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?FileUuid
    {
        if (null === $value) {
            return null;
        }

        return FileUuid::fromNative($value);
    }
}
