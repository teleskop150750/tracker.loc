<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\ValueObject;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\Boolean\Boolean;

#[Embeddable]
class TaskPublished extends Boolean
{
    #[Column(name: 'published', type: Types::BOOLEAN)]
    protected bool $value;

    public static function fromNative(bool $value): static
    {
        return new static($value);
    }
}
