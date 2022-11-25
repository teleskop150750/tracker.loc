<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject\DateTime;

use Modules\Shared\Domain\ValueObject\Util\Util;
use Modules\Shared\Domain\ValueObject\ValueObjectInterface;

class DateTime implements ValueObjectInterface
{
    public const MYSQL_DATETIME = 'Y-m-d H:i:s';
    public const W3C = 'Y-m-d\TH:i:sP';
    public const FRONTEND_FORMAT = 'Y-m-d';

    protected \DateTimeImmutable $value;

    public function __construct(\DateTimeImmutable $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->format(\DateTimeInterface::ATOM);
    }

    public static function now(): static
    {
        return new static(new \DateTimeImmutable());
    }

    public static function fromNative(\DateTimeImmutable $value): static
    {
        return new static($value);
    }

    public static function fromFormat(string $format, string $datetime): static
    {
        return new static(\DateTimeImmutable::createFromFormat($format, $datetime));
    }

    public function getDateTime(): \DateTimeImmutable
    {
        return $this->value;
    }

    public function format(string $format): string
    {
        return $this->value->format($format);
    }

    public function sameValueAs(object $valueObject): bool
    {
        if (false === Util::classEquals($this, $valueObject)) {
            return false;
        }

        return $this->__toString() === $valueObject->__toString();
    }
}
