<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;
use Webmozart\Assert\Assert;

#[Embeddable]
class FolderType extends StringLiteral
{
    public const DEFAULT = 'DEFAULT';
    public const ROOT = 'ROOT';

    #[Column(name: 'type', type: 'string')]
    protected string $value;

    public function __construct(string $value)
    {
        Assert::inArray($value, [
            self::DEFAULT,
            self::ROOT,
        ], 'Невалидный тип папки');

        parent::__construct($value);
    }
}
