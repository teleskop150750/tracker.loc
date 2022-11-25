<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject\String;

use Modules\Shared\Domain\ValueObject\Util\Util;
use Modules\Shared\Domain\ValueObject\ValueObjectInterface;

class NullableStringLiteral implements ValueObjectInterface
{
    protected null|string $value;

    public function __construct(string $value = null)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    /**
     * @return static
     */
    public static function fromNative(string $value = null)
    {
        return new self($value);
    }

    public function toNative(): ?string
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

    public function isNull(): bool
    {
        return null === $this->value;
    }

    public function isEmpty(): bool
    {
        return $this->isNull() || '' === $this->toNative();
    }
}
