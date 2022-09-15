<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObject;

interface ValueObjectInterface
{
    public function __toString(): string;

    public static function fromNative(mixed ...$values): mixed;

    public function sameValueAs(object $valueObject): bool;
}
