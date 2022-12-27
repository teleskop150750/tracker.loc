<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObject\Identity;

use Modules\Shared\Domain\ValueObject\ValueObjectInterface;

interface UUIDInterface extends \Stringable, ValueObjectInterface
{
    public function getId(): string;

    public static function generateRandom(): static;

    public static function fromNative(string $value): static;

    public function isEqualTo(object $valueObject): bool;

    public function isNotEqualTo(self $valueObject): bool;

    public function mustBeEqualTo(self $id): void;

    public function mustNotBeEqualTo(self $id): void;
}
