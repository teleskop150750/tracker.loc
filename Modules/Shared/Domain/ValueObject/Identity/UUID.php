<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject\Identity;

use Modules\Shared\Domain\ValueObject\Util\Util;
use Ramsey\Uuid\Nonstandard\Uuid as RamseyUuid;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

abstract class UUID implements UUIDInterface
{
    private string $id;

    /**
     * @throws InvalidArgumentException
     */
    final public function __construct(string $id)
    {
        Assert::true(RamseyUuid::isValid($id), 'Невалидный UUID: '.$id);

        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @throws InvalidArgumentException
     */
    final public static function generateRandom(): static
    {
        $id = RamseyUuid::uuid4()->toString();

        return new static($id);
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

    public function isNotEqualTo(UUIDInterface $valueObject): bool
    {
        return !$this->isEqualTo($valueObject);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function mustBeEqualTo(UUIDInterface $id): void
    {
        Assert::true($this->isEqualTo($id), 'Is not same id');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function mustNotBeEqualTo(UUIDInterface $id): void
    {
        Assert::true($this->isNotEqualTo($id), 'Is same id');
    }
}
