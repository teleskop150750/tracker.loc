<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Shared\Infrastructure\Doctrine\Types\UuidValueObjectType;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;

class TaskUuidType extends UuidValueObjectType
{
    protected const TYPE = 'task_uuid';

    /**
     * @param null|string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?TaskUuid
    {
        if (null === $value) {
            return null;
        }

        return TaskUuid::fromNative($value);
    }
}
