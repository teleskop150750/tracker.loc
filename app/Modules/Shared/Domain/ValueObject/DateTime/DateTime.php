<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObject\DateTime;

use App\Modules\Shared\Domain\ValueObject\Util\Util;
use App\Modules\Shared\Domain\ValueObject\ValueObjectInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

class DateTime implements ValueObjectInterface
{
    public const MYSQL_DATETIME = 'Y-m-d H:i:s';
    public const W3C = 'Y-m-d\TH:i:sP';

    protected DateTimeImmutable $value;

    public function __construct(DateTimeImmutable $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->format(DateTimeInterface::ATOM);
    }

    public static function now(): self
    {
        return new self(new DateTimeImmutable());
    }

    /**
     * @throws Exception
     */
    public static function fromNative(mixed ...$values): self
    {
        return new self(new DateTimeImmutable($values[0]));
    }

    public static function fromFormat(string $format, string $datetime): self
    {
        return new self(DateTimeImmutable::createFromFormat($format, $datetime));
    }

    public static function fromDateTimeImmutable(DateTimeImmutable $values): self
    {
        return new self($values[0]);
    }

    public function getDateTime(): DateTimeImmutable
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
