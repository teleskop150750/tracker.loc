<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Entity\Folder;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation\Tree;
use Gedmo\Mapping\Annotation\TreeClosure;
use Gedmo\Mapping\Annotation\TreeLevel;
use Gedmo\Mapping\Annotation\TreeParent;
use Gedmo\Tree\Entity\Repository\ClosureTreeRepository;
use Modules\Auth\User\Domain\Entity\User;
use Modules\Shared\Domain\AggregateRoot;
use Modules\Shared\Infrastructure\Doctrine\Traits\TimestampableEntity;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderName;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderPublished;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderUuid;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

#[Table(name: 'folders')]
#[Entity(repositoryClass: ClosureTreeRepository::class)]
#[Tree(type: 'closure')]
#[TreeClosure(class: FolderClosure::class)]
class Folder extends AggregateRoot
{
    use TimestampableEntity;

    #[Embedded(class: FolderName::class, columnPrefix: false)]
    protected FolderName $name;

    #[Embedded(class: FolderType::class, columnPrefix: false)]
    protected FolderType $type;

    #[Embedded(class: FolderPublished::class, columnPrefix: false)]
    protected FolderPublished $published;

    #[Id]
    #[Column(type: Types::STRING, length: 36)]
    private string|null $id;

    #[Column(name: 'level', type: Types::INTEGER, nullable: true)]
    #[TreeLevel]
    private int|null $level;

    #[ManyToOne(targetEntity: self::class, fetch: 'EXTRA_LAZY', inversedBy: 'children')]
    #[JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[TreeParent]
    private self|null $parent;

    /**
     * @var Collection<int, Folder>
     */
    #[OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    #[JoinColumn(onDelete: 'CASCADE')]
    private Collection $children;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'folders')]
    #[JoinColumn(name: 'author_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $author;

    /**
     * @var Collection<int, User>
     */
    #[ManyToMany(targetEntity: User::class, mappedBy: 'sharedFolders')]
    #[JoinTable(name: 'folder_shared')]
    private Collection $sharedUsers;

    /**
     * @var Collection<int, Task>
     */
    #[ManyToMany(targetEntity: Task::class, inversedBy: 'folders')]
    private Collection $tasks;

    /**
     * @var Collection<int, FolderClosure>
     */
    private Collection $closures;

    public function __construct(
        FolderUuid $uuid,
        FolderName $name,
        User $author,
        FolderType $type
    ) {
        $this->id = $uuid->getId();
        $this->setName($name);
        $this->setAuthor($author);
        $this->name = $name;
        $this->type = $type;
        $this->closures = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->sharedUsers = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->published = FolderPublished::fromNative(true);
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getUuid(): FolderUuid
    {
        return FolderUuid::fromNative($this->id);
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getType(): FolderType
    {
        return $this->type;
    }

    public function setType(FolderType $type): void
    {
        $this->type = $type;
    }

    /**
     * @return Collection<int, User>
     */
    public function getSharedUsers(): Collection
    {
        return $this->sharedUsers;
    }

    public function addSharedUser(User $shared): void
    {
        foreach ($this->sharedUsers as $item) {
            if ($item->isEqualTo($shared)) {
                return;
            }
        }

        $this->sharedUsers->add($shared);
        $shared->addSharedFolder($this);
    }

    public function removeSharedUser(User $shared): void
    {
        $removed = false;

        foreach ($this->sharedUsers as $key => $item) {
            if ($item->isEqualTo($shared)) {
                $this->sharedUsers->remove($key);
                $removed = true;

                break;
            }
        }

        if (false === $removed) {
            return;
        }

        $shared->removeSharedFolder($this);
    }

    public function setName(FolderName $name): void
    {
        $this->name = $name;
    }

    public function getName(): FolderName
    {
        return $this->name;
    }

    public function setParent(self $parent = null): void
    {
        $this->parent = $parent;

        $parent?->addChildren($this);
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function addClosure(FolderClosure $closure): void
    {
        $this->closures[] = $closure;
    }

    public function setLevel(?int $level): void
    {
        $this->level = $level;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    /**
     * @return Collection<int, Folder>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChildren(self $folder): void
    {
        foreach ($this->children as $item) {
            if ($item->isEqualTo($folder)) {
                return;
            }
        }

        $this->children->add($folder);
        $folder->setParent($this);
    }

    public function removeChildren(self $folder): void
    {
        foreach ($this->children as $kay => $item) {
            if ($item->isEqualTo($folder)) {
                $this->children->remove($kay);

                break;
            }
        }

        $folder->setParent(null);
    }

    public function getPublished(): FolderPublished
    {
        return $this->published;
    }

    public function setPublished(FolderPublished $published): void
    {
        $this->published = $published;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): void
    {
        foreach ($this->tasks as $item) {
            if ($item->isEqualTo($task)) {
                return;
            }
        }

        $this->tasks->add($task);
        $task->addFolder($this);
    }

    public function removeTask(Task $task): void
    {
        $removed = false;

        foreach ($this->tasks as $key => $item) {
            if ($item->isEqualTo($task)) {
                $this->tasks->remove($key);
                $removed = true;

                break;
            }
        }

        if (false === $removed) {
            return;
        }

        $task->removeFolder($this);
    }

    protected function getId(): ?string
    {
        return $this->id;
    }

    private function setAuthor(User $author): void
    {
        $this->author = $author;
    }
}
