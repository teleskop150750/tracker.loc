<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Services;

use App\Exceptions\HttpException;
use Modules\Tracker\Folder\Domain\Entity\Folder\Folder;
use Modules\Tracker\Folder\Domain\Entity\Folder\ValueObject\FolderPublished;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Task\Domain\Entity\Task\ValueObject\TaskPublished;

class UpdateFolderPublished
{
    private Folder $folder;
    private FolderRepositoryInterface $folderRepository;

    public function __construct(Folder $folder, FolderRepositoryInterface $folderRepository)
    {
        $this->folder = $folder;
        $this->folderRepository = $folderRepository;
    }

    public static function make(Folder $folder, FolderRepositoryInterface $folderRepository): static
    {
        return new static($folder, $folderRepository);
    }

    /**
     * @throws \Exception
     */
    public function updatePublished(FolderPublished $published): void
    {
        if ($this->folder->getPublished()->sameValueAs($published)) {
            return;
        }

        $publishedNative = $published->toNative();
        $parentPublished = $this->getParentPublished($this->folder);

        if (false === $parentPublished && true === $publishedNative) {
            return;
        }

        /** @var Folder[] $folders */
        $folders = [$this->folder, ...$this->folderRepository->children(node: $this->folder, includeTasks: true)];

        foreach ($folders as $item) {
            $item->setPublished(FolderPublished::fromNative($publishedNative));

            foreach ($item->getTasks() as $task) {
                $task->setPublished(TaskPublished::fromNative($publishedNative));
            }
        }
    }

    /**
     * @throws HttpException
     */
    private function getParentPublished(Folder $folder): bool
    {
        if (!$folder->getParent()) {
            throw new HttpException('Родитель не найден', 400, 400);
        }

        return $folder->getParent()->getPublished()->toNative();
    }
}
