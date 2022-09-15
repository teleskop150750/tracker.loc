<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Doctrine\Types;

use App\Modules\Shared\Domain\ValueObject\Identity\UUID;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class IdValueObjectType extends Type
{
    protected const TYPE = 'id_value_object';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    /**
     * @param ?UUID $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        return (string) $value;
    }

    public function getName(): string
    {
        return static::TYPE;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
