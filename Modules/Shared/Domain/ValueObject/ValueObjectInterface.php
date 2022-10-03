<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject;

interface ValueObjectInterface
{
    public function __toString(): string;

    public function sameValueAs(object $valueObject): bool;
}
