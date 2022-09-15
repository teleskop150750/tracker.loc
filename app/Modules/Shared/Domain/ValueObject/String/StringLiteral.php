<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObject\String;

use App\Modules\Shared\Domain\ValueObject\Util\Util;
use App\Modules\Shared\Domain\ValueObject\ValueObjectInterface;

class StringLiteral implements ValueObjectInterface
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->toNative();
    }

    public static function fromNative(mixed ...$values): self
    {
        return new self($values[0]);
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
