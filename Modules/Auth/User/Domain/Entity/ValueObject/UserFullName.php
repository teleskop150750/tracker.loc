<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\Util\Util;
use Modules\Shared\Domain\ValueObject\ValueObjectInterface;

#[Embeddable]
class UserFullName implements ValueObjectInterface
{
    #[Column(name: 'first_name', type: 'string')]
    private string $firstName;

    #[Column(name: 'last_name', type: 'string')]
    private string $lastName;

    #[Column(name: 'patronymic', type: 'string')]
    private string $patronymic;

    public function __construct(string $firstName, string $lastName, string $patronymic)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->patronymic = $patronymic;
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }

    public function getFullName(): string
    {
        return $this->getLastName().' '.$this->getFirstName().' '.$this->getPatronymic();
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getPatronymic(): string
    {
        return $this->patronymic;
    }

    public static function fromNative(string $firstName, string $lastName, string $patronymic): self
    {
        return new self($firstName, $lastName, $patronymic);
    }

    public function sameValueAs(object $valueObject): bool
    {
        if (false === Util::classEquals($this, $valueObject)) {
            return false;
        }

        return $this->getFullName() === $valueObject->getFullName();
    }
}
