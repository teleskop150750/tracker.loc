<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Illuminate\Support\Facades\Hash;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;
use Webmozart\Assert\Assert;

#[Embeddable]
class UserPassword extends StringLiteral
{
    #[Column(name: 'password', type: 'string')]
    protected string $value;

    public function __construct(string $value)
    {
        Assert::false(Hash::needsRehash($value), 'Пароль не должен храниться в открытом виде');
        parent::__construct($value);
    }

    public static function fromHash(string $password): self
    {
        return new self($password);
    }

    public static function fromPlainText(string $password): self
    {
        return new self(Hash::make($password));
    }
}
