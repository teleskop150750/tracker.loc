<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Shared\Infrastructure\Doctrine\Types\UuidValueObjectType;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;

final class FolderUuidType extends UuidValueObjectType
{
    protected const TYPE = 'folder_uuid';

    /**
     * @param null|string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?FolderUuid
    {
        if (null === $value) {
            return null;
        }

        return FolderUuid::fromNative($value);
    }
}
