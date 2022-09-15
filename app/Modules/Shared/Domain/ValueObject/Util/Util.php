<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObject\Util;

class Util
{
    public static function classEquals(object $objectA, object $objectB): bool
    {
        return \get_class($objectA) === \get_class($objectB);
    }

    public static function getClassAsString(mixed $object): string
    {
        return \get_class($object);
    }
}
