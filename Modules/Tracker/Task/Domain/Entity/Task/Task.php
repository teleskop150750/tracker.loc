<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Domain\Entity\Task;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Illuminate\Validation\ValidationException;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Shared\Domain\AggregateRoot;
use Modules\Shared\Domain\ValueObject\Identity\UUID;
use Modules\Shared\Infrastructure\Doctrine\Traits\TimestampableEntity;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskDescription;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskEndDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskImportance;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskName;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskPublished;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStartDate;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskStatus;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskUuid;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskWasStarted;
use Modules\Tracker\Task\Domain\Entity\TaskRelationship\TaskRelationship;

#[Entity]
class Task extends AggregateRoot
{
    use TimestampableEntity;

    #[Id]
    #[Column(name: 'id', type: 'task_uuid')]
    protected UUID $uuid;

    #[Embedded(class: TaskPublished::class, columnPrefix: false)]
    protected TaskPublished $published;

    #[Embedded(class: TaskWasStarted::class, columnPrefix: false)]
    protected TaskWasStarted $wasStarted;

    #[Embedded(class: TaskName::class, columnPrefix: false)]
    protected TaskName $name;

    #[Embedded(class: TaskStartDate::class, columnPrefix: false)]
    protected TaskStartDate $startDate;

    #[Embedded(class: TaskEndDate::class, columnPrefix: false)]
    protected TaskEndDate $endDate;

    #[Embedded(class: TaskStatus::class, columnPrefix: false)]
    protected TaskStatus $status;

    #[Embedded(class: TaskImportance::class, columnPrefix: false)]
    protected TaskImportance $importance;

    #[Embedded(class: TaskDescription::class, columnPrefix: false)]
    protected TaskDescription $description;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'tasks')]
    #[JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $author;

    #[ManyToOne(targetEntity: Folder::class, inversedBy: 'tasks')]
    #[JoinColumn(name: 'folder_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private null|Folder $folder;

    /**
     * @var Collection<int, TaskRelationship>
     */
    #[OneToMany(mappedBy: 'left', targetEntity: TaskRelationship::class, cascade: ['persist', 'remove'])]
    private Collection $taskRelationships;

    /**
     * @var Collection<int, TaskRelationship>
     */
    #[OneToMany(mappedBy: 'right', targetEntity: TaskRelationship::class, cascade: ['persist', 'remove'])]
    private Collection $inverseTaskRelationships;

    /**
     * @var Collection<int, User>
     */
    #[ManyToMany(targetEntity: User::class, mappedBy: 'assignedTasks')]
    #[JoinTable(name: 'task_executor')]
    private Collection $executors;

    public function __construct(
        TaskUuid $uuid,
        TaskName $name,
        User $author,
        TaskStartDate $startDate,
        TaskEndDate $endDate,
        Folder $folder,
        TaskStatus $status,
        TaskImportance $importance
    ) {
        $this->uuid = $uuid;
        $this->setName($name);
        $this->author = $author;
        $this->assertDate($startDate, $endDate);
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->description = TaskDescription::fromNative('');
        $this->taskRelationships = new ArrayCollection();
        $this->inverseTaskRelationships = new ArrayCollection();
        $this->executors = new ArrayCollection();
        $this->folder = $folder;
        $this->published = TaskPublished::fromNative(true);
        $this->wasStarted = TaskWasStarted::fromNative(true);
        $this->setStatus($status);
        $this->setImportance($importance);
    }

    public function __toString(): string
    {
        return (string) $this->uuid;
    }

    /**
     * @noinspection SenselessMethodDuplicationInspection
     */
    public function getUuid(): TaskUuid
    {
        return $this->uuid;
    }

    public function getPublished(): TaskPublished
    {
        return $this->published;
    }

    public function setPublished(TaskPublished $published): void
    {
        $this->published = $published;
    }

    public function getWasStarted(): TaskWasStarted
    {
        return $this->wasStarted;
    }

    public function setWasStarted(TaskWasStarted $wasStarted): void
    {
        $this->wasStarted = $wasStarted;
    }

    public function getName(): TaskName
    {
        return $this->name;
    }

    public function setName(TaskName $name): void
    {
        $this->name = $name;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getStartDate(): TaskStartDate
    {
        return $this->startDate;
    }

    public function setStartDate(TaskStartDate $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): TaskEndDate
    {
        return $this->endDate;
    }

    public function setEndDate(TaskEndDate $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getDescription(): TaskDescription
    {
        return $this->description;
    }

    public function setDescription(TaskDescription $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function setStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    public function getImportance(): TaskImportance
    {
        return $this->importance;
    }

    public function setImportance(TaskImportance $importance): void
    {
        $this->importance = $importance;
    }

    /**
     * @return Collection<int, User>
     */
    public function getExecutors(): Collection
    {
        return $this->executors;
    }

    public function addExecutor(User $executor): void
    {
        foreach ($this->executors as $item) {
            if ($item->isEqualTo($executor)) {
                return;
            }
        }

        $this->executors->add($executor);
        $executor->addAssignedTasks($this);
    }

    public function removeExecutor(User $executor): void
    {
        $removed = false;

        foreach ($this->executors as $key => $item) {
            if ($item->isEqualTo($executor)) {
                $this->executors->remove($key);
                $removed = true;

                break;
            }
        }

        if (false === $removed) {
            return;
        }

        $executor->removeAssignedTasks($this);
    }

    /**
     * @return Collection<int, TaskRelationship>
     */
    public function getTaskRelationships(): Collection
    {
        return $this->taskRelationships;
    }

    /**
     * @return Collection<int, TaskRelationship>
     */
    public function getInverseTaskRelationships(): Collection
    {
        return $this->inverseTaskRelationships;
    }

//    public function addTaskRelationship(TaskRelationship $taskRelationship): void
//    {
//        foreach ($this->taskRelationships as $key => $item) {
//            if ($item->isEqualTo($taskRelationship)) {
//                $this->taskRelationships->remove($key);
//
//                break;
//            }
//        }
//    }
//
//    public function removeTaskRelationship(TaskRelationship $taskRelationship): void
//    {
//        foreach ($this->taskRelationships as $kay => $item) {
//            if ($item->isEqualTo($taskRelationship)) {
//                $this->taskRelationships->remove($kay);
//
//                break;
//            }
//        }
//    }

    public function getFolder(): ?Folder
    {
        return $this->folder;
    }

    public function setFolder(Folder $folder): void
    {
        if ($this->folder->isEqualTo($folder)) {
            return;
        }

        $this->removeFolder();
        $this->folder = $folder;
        $folder->addTask($this);
    }

    public function removeFolder(): void
    {
        if ($this->folder) {
            $this->folder->removeTask($this);
            $this->folder = null;
        }
    }

    private function assertDate(TaskStartDate $startDate, TaskEndDate $endDate): void
    {
        $startDateTime = $startDate->getDateTime();
        $endDateTime = $endDate->getDateTime();

        if ($startDateTime > $endDateTime) {
            throw ValidationException::withMessages(
                [
                    'endDate' => 'Дата окончания не должна быть позже даны начала.',
                ]
            );
        }
    }
}