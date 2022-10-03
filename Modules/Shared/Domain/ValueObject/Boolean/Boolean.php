<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject\Boolean;

use Modules\Shared\Domain\ValueObject\Util\Util;
use Modules\Shared\Domain\ValueObject\ValueObjectInterface;

class Boolean implements ValueObjectInterface
{
    protected bool $value;

    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string) $this->toNative();
    }

    public static function fromNative(bool $value): static
    {
        return new static($value);
    }

    public function toNative(): bool
    {
        return $this->value;
    }

    public function sameValueAs(object $valueObject): bool
    {
        if (false === Util::classEquals($this, $valueObject)) {
            return false;
        }

        return $this->toNative() === $valueObject->toNative();
    }
}
