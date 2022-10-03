<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;

#[Embeddable]
class FolderName extends StringLiteral
{
    public const DEFAULT = 'Личное';
    #[Column(name: 'name', type: 'string')]
    protected string $value;
}
