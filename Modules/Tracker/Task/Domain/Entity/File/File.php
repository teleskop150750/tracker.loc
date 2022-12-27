<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\File;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Modules\Shared\Domain\AggregateRoot;
use Modules\Shared\Infrastructure\Doctrine\Traits\TimestampableEntity;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileName;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileOriginName;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FilePath;
use Modules\Tracker\Task\Domain\Entity\File\ValueObject\FileUuid;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

#[Entity]
class File extends AggregateRoot
{
    use TimestampableEntity;

    #[Id]
    #[Column(name: 'id', type: 'file_uuid')]
    protected FileUuid $uuid;

    #[Embedded(class: FileOriginName::class, columnPrefix: false)]
    protected FileOriginName $originName;

    #[Embedded(class: FilePath::class, columnPrefix: false)]
    protected FilePath $path;

    #[ManyToOne(targetEntity: Task::class, inversedBy: 'files')]
    #[JoinColumn(name: 'task_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private null|Task $task;

    public function __construct(FileUuid $uuid, Task $task, FileOriginName $originName, FilePath $path)
    {
        $this->uuid = $uuid;
        $this->task = $task;
        $this->originName = $originName;
        $this->path = $path;
    }

    public function getUuid(): FileUuid
    {
        return $this->uuid;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): void
    {
        if ($this->task->isEqualTo($task)) {
            return;
        }

        $this->removeTask();
        $this->task = $task;
        $task->addFile($this);
    }

    public function removeTask(): void
    {
        if ($this->task) {
            $this->task->removeFile($this);
            $this->task = null;
        }
    }

    public function getName(): FileName
    {
        return $this->name;
    }

    public function getOriginName(): FileOriginName
    {
        return $this->originName;
    }

    public function getPath(): FilePath
    {
        return $this->path;
    }
}
