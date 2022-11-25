<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\File\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;

#[Embeddable]
class FileOriginName extends StringLiteral
{
    #[Column(name: 'origin_name', type: 'string')]
    protected string $value;
}
