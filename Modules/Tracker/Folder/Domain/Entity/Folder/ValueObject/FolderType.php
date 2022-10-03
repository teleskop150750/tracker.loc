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
    public const PUBLIC_ROOT = 'PUBLIC_ROOT';

    #[Column(name: 'type', type: 'string')]
    protected string $value;

    public function __construct(string $value)
    {
        Assert::inArray($value, [
            self::DEFAULT,
            self::ROOT,
            self::PUBLIC_ROOT,
        ], 'Невалидный тип папки');

        parent::__construct($value);
    }
}
