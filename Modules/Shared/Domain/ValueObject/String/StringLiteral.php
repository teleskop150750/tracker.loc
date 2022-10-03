<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject\String;

use Modules\Shared\Domain\ValueObject\Util\Util;
use Modules\Shared\Domain\ValueObject\ValueObjectInterface;

class StringLiteral implements ValueObjectInterface
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = trim($value);
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    public static function fromNative(string $value): static
    {
        return new static($value);
    }

    public function toNative(): string
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

    public function isEmpty(): bool
    {
        return '' === $this->toNative();
    }
}
