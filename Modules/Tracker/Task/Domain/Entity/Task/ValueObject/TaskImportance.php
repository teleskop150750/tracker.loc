<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;
use Webmozart\Assert\Assert;

#[Embeddable]
class TaskImportance extends StringLiteral
{
    public const HIGH = 'HIGH';
    public const NORMAL = 'NORMAL';
    public const LOW = 'LOW';

    public const LABEL_KEY = 'LABEL';
    public const TYPE_KEY = 'TYPE';

    public const IMPORTANCE_INFO = [
        self::HIGH => [
            self::TYPE_KEY => self::HIGH,
            self::LABEL_KEY => 'Высокая важность',
        ],
        self::NORMAL => [
            self::TYPE_KEY => self::NORMAL,
            self::LABEL_KEY => 'Обычная важность',
        ],
        self::LOW => [
            self::TYPE_KEY => self::LOW,
            self::LABEL_KEY => 'Низкая важность',
        ],
    ];

    #[Column(name: 'importance', type: 'string')]
    protected string $value;

    public function __construct(string $value)
    {
        Assert::inArray($value, [
            self::HIGH,
            self::NORMAL,
            self::LOW,
        ], 'Невалидный тип: '.$value);

        parent::__construct($value);
    }

    public function getType(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return self::IMPORTANCE_INFO[$this->value][self::LABEL_KEY];
    }

    /**
     * @return string[][]
     */
    public function getInfo(): array
    {
        return self::IMPORTANCE_INFO;
    }
}
