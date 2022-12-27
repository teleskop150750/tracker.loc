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

    public const LABEL_KEY = 'LABEL';
    public const TYPE_KEY = 'TYPE';

    #[Column(name: 'type', type: 'string')]
    protected string $value;

    public function __construct(string $value)
    {
        Assert::inArray($value, [
            self::END_START,
        ], 'Невалидный тип связи: '.$value);

        parent::__construct($value);
    }

    public function getType(): string
    {
        return $this->value;
    }
}
