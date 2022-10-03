<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;
use Webmozart\Assert\Assert;

#[Embeddable]
class TaskRelationshipType extends StringLiteral
{
    public const END_START = 'END_START';
    public const START_START = 'START_START';
    public const END_END = 'END_END';
    public const START_END = 'START_END';

    public const LABEL_KEY = 'LABEL';
    public const TYPE_KEY = 'TYPE';

    public const TYPE_INFO = [
        self::END_START => [
            self::TYPE_KEY => self::END_START,
            self::LABEL_KEY => 'Конец-начало',
        ],
        self::START_START => [
            self::TYPE_KEY => self::START_START,
            self::LABEL_KEY => 'Начало-начало',
        ],
        self::END_END => [
            self::TYPE_KEY => self::END_END,
            self::LABEL_KEY => 'Конец-конец',
        ],
        self::START_END => [
            self::TYPE_KEY => self::START_END,
            self::LABEL_KEY => 'Начало-конец',
        ],
    ];

    #[Column(name: 'type', type: 'string')]
    protected string $value;

    public function __construct(string $value)
    {
        Assert::inArray($value, [
            self::END_START,
            self::START_START,
            self::END_END,
            self::START_END,
        ], 'Невалидный тип доступа: '.$value);

        parent::__construct($value);
    }

    public function getType(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return self::TYPE_INFO[$this->value][self::LABEL_KEY];
    }

    /**
     * @return string[][]
     */
    public function getInfo(): array
    {
        return self::TYPE_INFO;
    }
}
