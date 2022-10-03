<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\TaskRelationship;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Modules\Shared\Domain\AggregateRoot;
use Modules\Shared\Domain\ValueObject\Identity\UUID;
use Modules\Shared\Infrastructure\Doctrine\Traits\TimestampableEntity;
use Modules\Tracker\Task\Domain\Entity\Task\Task;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipType;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\ValueObject\TaskRelationshipUuid;

#[Entity]
class TaskRelationship extends AggregateRoot
{
    use TimestampableEntity;

    #[Id]
    #[Column(name: 'id', type: 'task_relationship_uuid')]
    protected UUID $uuid;

    #[Embedded(class: TaskRelationshipType::class, columnPrefix: false)]
    protected TaskRelationshipType $type;

    #[ManyToOne(targetEntity: Task::class, inversedBy: 'taskRelationships')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Task $left;

    #[ManyToOne(targetEntity: Task::class, inversedBy: 'inverseTaskRelationships')]
    #[JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Task $right;

    public function __construct(TaskRelationshipUuid $uuid, Task $left, Task $right, TaskRelationshipType $type)
    {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->left = $left;
        $this->right = $right;
    }

    public function __toString(): string
    {
        return (string) $this->uuid;
    }

    /**
     * @noinspection SenselessMethodDuplicationInspection
     */
    public function getUuid(): TaskRelationshipUuid
    {
        return $this->uuid;
    }

    public function getLeft(): Task
    {
        return $this->left;
    }

    public function getRight(): Task
    {
        return $this->right;
    }

    public function getType(): TaskRelationshipType
    {
        return $this->type;
    }
}
