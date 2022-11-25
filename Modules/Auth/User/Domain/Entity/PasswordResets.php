<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Modules\Auth\User\Domain\Entity\ValueObject\PasswordToken;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Shared\Infrastructure\Doctrine\Traits\TimestampableEntity;

#[Entity]
class PasswordResets
{
    use TimestampableEntity;

    #[Id]
    #[GeneratedValue]
    #[Column(name: 'id')]
    protected int $id;

    #[Embedded(class: UserEmail::class, columnPrefix: false)]
    protected UserEmail $email;

    #[Embedded(class: PasswordToken::class, columnPrefix: false)]
    protected PasswordToken $token;

    public function __construct(UserEmail $email, PasswordToken $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    public function getEmail(): UserEmail
    {
        return $this->email;
    }

    public function getToken(): PasswordToken
    {
        return $this->token;
    }
}
