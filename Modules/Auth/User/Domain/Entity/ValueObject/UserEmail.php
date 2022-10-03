<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;
use Webmozart\Assert\Assert;

#[Embeddable]
class UserEmail extends StringLiteral
{
    #[Column(name: 'email', type: 'string', unique: true)]
    protected string $value;

    public function __construct(string $value)
    {
        parent::__construct($value);
        Assert::email($value, 'Невалидный email');
    }

    public static function fromNative(string $value): static
    {
        return new static($value);
    }
}
