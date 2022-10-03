<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;

#[Embeddable]
class TaskName extends StringLiteral
{
    #[Column(name: 'name', type: 'string')]
    protected string $value;
}
