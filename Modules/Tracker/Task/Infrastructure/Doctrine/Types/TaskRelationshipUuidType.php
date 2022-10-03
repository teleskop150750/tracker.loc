<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Infrastructure\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Shared\Infrastructure\Doctrine\Types\UuidValueObjectType;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipUuid;

class TaskRelationshipUuidType extends UuidValueObjectType
{
    protected const TYPE = 'task_relationship_uuid';

    /**
     * @param null|string $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?TaskRelationshipUuid
    {
        if (null === $value) {
            return null;
        }

        return TaskRelationshipUuid::fromNative($value);
    }
}
