<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;
use Webmozart\Assert\Assert;

#[Embeddable]
class FolderAccess extends StringLiteral
{
    public const PRIVATE = 'PRIVATE';
    public const PUBLIC = 'PUBLIC';
    public const LIMIT = 'LIMIT';

    public const LABEL_KEY = 'LABEL';
    public const TYPE_KEY = 'TYPE';

    public const ACCESS_INFO = [
        self::PRIVATE => [
            self::TYPE_KEY => self::PRIVATE,
            self::LABEL_KEY => 'Личный',
        ],
        self::LIMIT => [
            self::TYPE_KEY => self::LIMIT,
            self::LABEL_KEY => 'Ограниченные доступ',
        ],
        self::PUBLIC => [
            self::TYPE_KEY => self::PUBLIC,
            self::LABEL_KEY => 'Доступно всем',
        ],
    ];

    #[Column(name: 'access', type: 'string')]
    protected string $value;

    public function __construct(string $value)
    {
        Assert::inArray($value, [
            self::PRIVATE,
            self::PUBLIC,
            self::LIMIT,
        ], 'Невалидный тип доступа: '.$value);

        parent::__construct($value);
    }

    public function getType(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return self::ACCESS_INFO[$this->value][self::LABEL_KEY];
    }

    /**
     * @return string[][]
     */
    public function getAllAccessTypes(): array
    {
        return self::ACCESS_INFO;
    }
}
