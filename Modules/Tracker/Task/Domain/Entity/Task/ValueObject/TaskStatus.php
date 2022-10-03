<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\ValueObject;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\String\StringLiteral;
use Webmozart\Assert\Assert;

#[Embeddable]
class TaskStatus extends StringLiteral
{
    public const NEW = 'NEW';
    public const IN_WORK = 'IN_WORK';
    public const DONE = 'DONE';
    public const WAITING = 'WAITING';
    public const CANCELLED = 'CANCELLED';

    public const LABEL_KEY = 'LABEL';
    public const TYPE_KEY = 'TYPE';
    public const COLOR_KEY = 'COLOR';

    public const STATUS_INFO = [
        self::NEW => [
            self::LABEL_KEY => 'Новое',
            self::TYPE_KEY => self::NEW,
            self::COLOR_KEY => '207deg 90% 54%',
        ],
        self::IN_WORK => [
            self::LABEL_KEY => 'В работе',
            self::TYPE_KEY => self::IN_WORK,
            self::COLOR_KEY => '193deg 54% 62%',
        ],
        self::DONE => [
            self::LABEL_KEY => 'Выполнено',
            self::TYPE_KEY => self::DONE,
            self::COLOR_KEY => '193deg 54% 62%',
        ],
        self::WAITING => [
            self::LABEL_KEY => 'В ожидании',
            self::TYPE_KEY => self::WAITING,
            self::COLOR_KEY => '80deg 48% 55%',
        ],
        self::CANCELLED => [
            self::LABEL_KEY => 'Отменено',
            self::TYPE_KEY => self::CANCELLED,
            self::COLOR_KEY => '80deg 48% 55%',
        ],
    ];

    #[Column(name: 'status', type: 'string')]
    protected string $value;

    public function __construct(string $value)
    {
        Assert::inArray($value, [
            self::NEW,
            self::IN_WORK,
            self::DONE,
            self::WAITING,
            self::CANCELLED,
        ], 'Невалидный тип: '.$value);

        parent::__construct($value);
    }

    public function getType(): string
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        return self::STATUS_INFO[$this->value][self::LABEL_KEY];
    }

    /**
     * @return string[][]
     */
    public function getInfo(): array
    {
        return self::STATUS_INFO;
    }
}
