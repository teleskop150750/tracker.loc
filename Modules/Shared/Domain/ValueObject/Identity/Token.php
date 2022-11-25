<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject\Identity;

use Modules\Shared\Domain\ValueObject\Util\Util;
use Modules\Shared\Domain\ValueObject\ValueObjectInterface;
use Ramsey\Uuid\Nonstandard\Uuid as RamseyUuid;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

abstract class Token implements \Stringable, ValueObjectInterface
{
    protected string $value;

    /**
     * @throws InvalidArgumentException
     */
    final public function __construct(string $value)
    {
        Assert::true(RamseyUuid::isValid($value), 'Невалидный UUID: '.$value);

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getToken(): string
    {
        return $this->value;
    }

    /**
     * @throws InvalidArgumentException
     */
    final public static function generateRandom(): static
    {
        $value = RamseyUuid::uuid4()->toString();

        return new static($value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromNative(string $value): static
    {
        return new static($value);
    }

    public function sameValueAs(object $valueObject): bool
    {
        return $this->isEqualTo($valueObject);
    }

    public function isEqualTo(object $valueObject): bool
    {
        if (false === Util::classEquals($this, $valueObject)) {
            return false;
        }

        return $this->__toString() === $valueObject->__toString();
    }

    public function isNotEqualTo(self $valueObject): bool
    {
        return !$this->isEqualTo($valueObject);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function mustBeEqualTo(self $id): void
    {
        Assert::true($this->isEqualTo($id), 'Is not same id');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function mustNotBeEqualTo(self $id): void
    {
        Assert::true($this->isNotEqualTo($id), 'Is same id');
    }
}
