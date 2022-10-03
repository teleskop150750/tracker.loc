<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\ValueObject;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;

#[Embeddable]
class TaskDescription extends StringLiteral
{
    #[Column(name: 'description', type: Types::TEXT)]
    protected string $value;
}
