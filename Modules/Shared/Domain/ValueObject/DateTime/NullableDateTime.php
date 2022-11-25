<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject\DateTime;

use Modules\Shared\Domain\ValueObject\Util\Util;
use Modules\Shared\Domain\ValueObject\ValueObjectInterface;

class NullableDateTime implements ValueObjectInterface
{
    public const MYSQL_FORMAT = 'Y-m-d H:i:s';
    protected ?\DateTimeImmutable $value;

    public function __construct(\DateTimeImmutable $value = null)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value->format(self::MYSQL_FORMAT);
    }

    public function getValue(): ?\DateTimeImmutable
    {
        return $this->value;
    }

    public static function now(): static
    {
        return new static(new \DateTimeImmutable());
    }

    public static function fromNative(\DateTimeImmutable $value = null): static
    {
        return new static($value);
    }

    public static function fromFormat(string $format, string $datetime = null): self
    {
        if (null === $datetime) {
            return new self(null);
        }

        return new self(\DateTimeImmutable::createFromFormat($format, $datetime));
    }

    public function sameValueAs(object $valueObject): bool
    {
        if (false === Util::classEquals($this, $valueObject)) {
            return false;
        }

        return $this->__toString() === $valueObject->__toString();
    }
}
