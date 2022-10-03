<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Entity\Folder;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Tree\Entity\MappedSuperclass\AbstractClosure;

#[Entity]
#[Table(name: 'folder_closure')]
#[UniqueConstraint(name: 'folder_unique_idx', columns: ['ancestor', 'descendant'])]
#[Index(columns: ['depth'], name: 'folder_depth_idx')]
class FolderClosure extends AbstractClosure
{
    /**
     * @var null|Folder
     */
    #[ManyToOne(targetEntity: Folder::class)]
    #[JoinColumn(name: 'ancestor', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $ancestor;

    /**
     * @var null|Folder
     */
    #[ManyToOne(targetEntity: Folder::class)]
    #[JoinColumn(name: 'descendant', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected $descendant;
}
