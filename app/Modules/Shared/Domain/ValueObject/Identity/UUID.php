<?php

declare(strict_types=1);

namespace App\Modules\Shared\Domain\ValueObject\Identity;

use Assert\Assertion;
use Assert\AssertionFailedException;
use Ramsey\Uuid\Nonstandard\Uuid as RamseyUuid;
use RuntimeException;

abstract class UUID implements \Stringable
{
    public string $id;

    final public function __construct(string $id)
    {
        if (!RamseyUuid::isValid($id)) {
            throw new RuntimeException($id);
        }

        $this->id = $id;
    }

    public function __toString(): string
    {
        return $this->id;
    }

    final public static function generateRandom(): static
    {
        $id = RamseyUuid::uuid4()->toString();

        return new static($id);
    }

    final public static function fromString(string $id): static
    {
        return new static($id);
    }

    public function isEqualTo(self $id): bool
    {
        return $this->id === $id->id;
    }

    public function isNotEqualTo(self $id): bool
    {
        return $this->id !== $id->id;
    }

    /**
     * @throws AssertionFailedException
     */
    public function mustBeEqualTo(self $id): void
    {
        Assertion::true($this->isEqualTo($id), 'Is not same id');
    }

    /**
     * @throws AssertionFailedException
     */
    public function mustNotBeEqualTo(self $id): void
    {
        Assertion::true($this->isNotEqualTo($id), 'Is same id');
    }
}
