<?php

declare(strict_types=1);

namespace Modules\Auth\User\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\OneToMany;
use Modules\Auth\User\Domain\Entity\ValueObject\UserAvatar;
use Modules\Auth\User\Domain\Entity\ValueObject\UserDepartment;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmail;
use Modules\Auth\User\Domain\Entity\ValueObject\UserEmailVerifiedAt;
use Modules\Auth\User\Domain\Entity\ValueObject\UserFullName;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPassword;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPhone;
use Modules\Auth\User\Domain\Entity\ValueObject\UserPost;
use Modules\Auth\User\Domain\Entity\ValueObject\UserUuid;
use Modules\Shared\Domain\AggregateRoot;
use Modules\Shared\Infrastructure\Doctrine\Traits\TimestampableEntity;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Task\Domain\Entity\Task\Task;

#[Entity]
class User extends AggregateRoot
{
    use TimestampableEntity;

    #[Id]
    #[Column(name: 'id', type: 'user_uuid')]
    protected UserUuid $uuid;

    #[Embedded(class: UserEmail::class, columnPrefix: false)]
    protected UserEmail $email;

    #[Embedded(class: UserEmailVerifiedAt::class, columnPrefix: false)]
    protected UserEmailVerifiedAt $emailVerifiedAt;

    #[Embedded(class: UserFullName::class, columnPrefix: false)]
    protected UserFullName $fullName;

    #[Embedded(class: UserAvatar::class, columnPrefix: false)]
    protected UserAvatar $avatar;

    #[Embedded(class: UserPhone::class, columnPrefix: false)]
    protected UserPhone $phone;

    #[Embedded(class: UserPassword::class, columnPrefix: false)]
    protected UserPassword $password;

    #[Embedded(class: UserDepartment::class, columnPrefix: false)]
    private UserDepartment $department;

    #[Embedded(class: UserPost::class, columnPrefix: false)]
    private UserPost $post;

    /**
     * @var Collection<int, Folder>
     */
    #[OneToMany(mappedBy: 'author', targetEntity: Folder::class)]
    private Collection $folders;

    /**
     * @var Collection<int, Folder>
     */
    #[ManyToMany(targetEntity: Folder::class, inversedBy: 'sharedUsers')]
    private Collection $sharedFolders;

    /**
     * @var Collection<int, Task>
     */
    #[OneToMany(mappedBy: 'author', targetEntity: Task::class)]
    private Collection $tasks;

    /**
     * @var Collection<int, Task>
     */
    #[ManyToMany(targetEntity: Task::class, inversedBy: 'executors')]
    private Collection $assignedTasks;

    public function __construct(UserUuid $uuid, UserEmail $email, UserFullName $fullName, UserPassword $password)
    {
        $this->uuid = $uuid;
        $this->email = $email;
        $this->fullName = $fullName;
        $this->password = $password;
        $this->avatar = new UserAvatar();
        $this->tasks = new ArrayCollection();
        $this->assignedTasks = new ArrayCollection();
        $this->sharedFolders = new ArrayCollection();
        $this->folders = new ArrayCollection();
    }

    public function getUuid(): UserUuid
    {
        return $this->uuid;
    }

    public function getEmail(): UserEmail
    {
        return $this->email;
    }

    public function getEmailVerifiedAt(): UserEmailVerifiedAt
    {
        return $this->emailVerifiedAt;
    }

    public function setEmailVerifiedAt(UserEmailVerifiedAt $emailVerifiedAt): void
    {
        $this->emailVerifiedAt = $emailVerifiedAt;
    }

    public function getFullName(): UserFullName
    {
        return $this->fullName;
    }

    public function setFullName(UserFullName $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getAvatar(): UserAvatar
    {
        return $this->avatar;
    }

    public function setAvatar(UserAvatar $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getPhone(): UserPhone
    {
        return $this->phone;
    }

    public function setPhone(UserPhone $phone): void
    {
        $this->phone = $phone;
    }

    public function getPost(): UserPost
    {
        return $this->post;
    }

    public function setPost(UserPost $post): void
    {
        $this->post = $post;
    }

    public function getDepartment(): UserDepartment
    {
        return $this->department;
    }

    public function setDepartment(UserDepartment $department): void
    {
        $this->department = $department;
    }

    public function getPassword(): UserPassword
    {
        return $this->password;
    }

    public function setPassword(UserPassword $password): void
    {
        $this->password = $password;
    }

    /**
     * @return Collection<int, Folder>
     */
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function addFolder(Folder $folder): void
    {
        foreach ($this->folders as $item) {
            if ($item->isEqualTo($folder)) {
                return;
            }
        }

        $this->folders->add($folder);
    }

    /**
     * @return Collection<int, Folder>
     */
    public function getSharedFolders(): Collection
    {
        return $this->sharedFolders;
    }

    public function addSharedFolder(Folder $folder): void
    {
        foreach ($this->sharedFolders as $item) {
            if ($item->isEqualTo($folder)) {
                return;
            }
        }

        $this->sharedFolders->add($folder);
        $folder->addSharedUser($this);
    }

    public function removeSharedFolder(Folder $folder): void
    {
        $removed = false;

        foreach ($this->sharedFolders as $key => $item) {
            if ($item->isEqualTo($folder)) {
                $this->sharedFolders->remove($key);
                $removed = true;

                break;
            }
        }

        if (false === $removed) {
            return;
        }

        $folder->removeSharedUser($this);
    }

    /**
     * @return Collection<int, Folder>
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
    }

    /**
     * @return Collection<int, Task>
     */
    public function getAssignedTasks(): Collection
    {
        return $this->assignedTasks;
    }

    public function addAssignedTasks(Task $task): void
    {
        foreach ($this->assignedTasks as $item) {
            if ($item->isEqualTo($task)) {
                return;
            }
        }

        $this->assignedTasks->add($task);
        $task->addExecutor($this);
    }

    public function removeAssignedTasks(Task $task): void
    {
        $removed = false;

        foreach ($this->assignedTasks as $key => $item) {
            if ($item->isEqualTo($task)) {
                $this->assignedTasks->remove($key);
                $removed = true;

                break;
            }
        }

        if (false === $removed) {
            return;
        }

        $task->removeExecutor($this);
    }
}
