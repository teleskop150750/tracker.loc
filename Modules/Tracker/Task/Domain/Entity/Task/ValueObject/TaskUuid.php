<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task\ValueObject;

use Modules\Shared\Domain\ValueObject\Identity\UUID;
use Modules\Shared\Domain\ValueObject\Identity\UUIDInterface;

final class TaskUuid extends UUID implements UUIDInterface
{
}
