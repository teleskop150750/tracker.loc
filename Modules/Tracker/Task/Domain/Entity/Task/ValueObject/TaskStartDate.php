<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\ValueObject;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Modules\Shared\Domain\ValueObject\DateTime\DateTime;

#[Embeddable]
class TaskStartDate extends DateTime
{
    #[Column(name: 'start_date', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected \DateTimeImmutable $value;
}
