<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Services;

use App\Exceptions\HttpException;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderAccess;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderType;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;

class UpdateFolderAccess
{
    private FolderAccess $accessPublic;
    private FolderAccess $accessLimit;
    private FolderAccess $accessPrivate;
    private FolderType $typeDefault;
    private FolderType $typePublicRoot;
    private Folder $folder;
    private FolderRepositoryInterface $folderRepository;

    public function __construct(Folder $folder, FolderRepositoryInterface $folderRepository)
    {
        $this->folder = $folder;
        $this->folderRepository = $folderRepository;

        $this->accessPublic = FolderAccess::fromNative(FolderAccess::PUBLIC);
        $this->accessLimit = FolderAccess::fromNative(FolderAccess::LIMIT);
        $this->accessPrivate = FolderAccess::fromNative(FolderAccess::PRIVATE);
        $this->typeDefault = FolderType::fromNative(FolderType::DEFAULT);
        $this->typePublicRoot = FolderType::fromNative(FolderType::PUBLIC_ROOT);
    }

    public static function make(Folder $folder, FolderRepositoryInterface $folderRepository): static
    {
        return new static($folder, $folderRepository);
    }

    public function updateAccess(FolderAccess $access): void
    {
        if ($access->sameValueAs($this->accessPublic)) {
            $this->setPublic();
        } elseif ($access->sameValueAs($this->accessLimit)) {
            $this->setLimit();
        } elseif ($access->sameValueAs($this->accessPrivate)) {
            $this->setPrivate();
        }
    }

    private function setPublic(): void
    {
        if ($this->folder->getAccess()->sameValueAs($this->accessPublic)) {
            return;
        }

        $parent = $this->getParent();

        if ($parent->getAccess()->sameValueAs($this->accessPublic)) {
            return;
//            throw new \Exception('Родитель уже Public');
        }

        $children = $this->folderRepository->children($this->folder, includeSharedUsers: true);

        $this->folder->setAccess($this->accessPublic);
        $this->folder->setType($this->typePublicRoot);
        $users = $this->folder->getSharedUsers();

        foreach ($users as $user) {
            $user->removeSharedFolder($this->folder);
        }

        foreach ($children as $child) {
            $child->setAccess($this->accessPublic);
            $child->setType($this->typeDefault);

            $users = $child->getSharedUsers();

            foreach ($users as $user) {
                $user->removeSharedFolder($child);
            }
        }
    }

    private function setLimit(): void
    {
        $currentAccess = $this->folder->getAccess();

        if ($currentAccess->sameValueAs($this->accessLimit)) {
            return;
        }

        $parent = $this->getParent();

        if ($parent->getAccess()->sameValueAs($this->accessPublic)) {
            return;
//            throw new \Exception('Родитель Public');
        }

        $this->folder->setAccess($this->accessLimit);

        if ($currentAccess->sameValueAs($this->accessPublic)) {
            $this->folder->setType($this->typeDefault);
            $nodeChildren = $this->folder->getChildren();

            foreach ($nodeChildren as $child) {
                $child->setType($this->typePublicRoot);
            }
        } else {
            /** @var Folder[] $children */
            $children = $this->folderRepository->children($this->folder);

            foreach ($children as $child) {
                if ($child->getAccess()->sameValueAs($this->accessPrivate)) {
                    $child->setAccess($this->accessLimit);
                }
            }
        }
    }

    private function setPrivate(): void
    {
        $currentAccess = $this->folder->getAccess();

        if ($currentAccess->sameValueAs($this->accessPrivate)) {
            return;
        }

        $parent = $this->getParent();

        if (!$parent->getAccess()->sameValueAs($this->accessPrivate)) {
            return;
//            throw new \Exception('Родитель не Private');
        }

        $this->folder->setAccess($this->accessPrivate);

        if ($currentAccess->sameValueAs($this->accessPublic)) {
            $this->folder->setType($this->typeDefault);
            $nodeChildren = $this->folder->getChildren();

            foreach ($nodeChildren as $child) {
                $child->setType($this->typePublicRoot);
            }
        } else {
            $users = $this->folder->getSharedUsers();

            foreach ($users as $user) {
                $user->removeSharedFolder($this->folder);
            }

            /** @var Folder[] $children */
            $nodeChildren = $this->folder->getChildren();

            foreach ($nodeChildren as $child) {
                foreach ($users as $user) {
                    $user->addSharedFolder($child);
                }
            }
        }
    }

    /**
     * @throws HttpException
     */
    private function getParent(): Folder
    {
        $parent = $this->folder->getParent();

        if (!$parent) {
            throw new HttpException('Родитель не найден', 400, 400);
        }

        return $parent;
    }
}
