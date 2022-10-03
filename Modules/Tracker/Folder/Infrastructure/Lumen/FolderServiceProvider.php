<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Infrastructure\Lumen;

use Illuminate\Support\ServiceProvider;
use Modules\Tracker\Folder\Application\Command\CreateFolder\CreateFolderCommandHandler;
use Modules\Tracker\Folder\Application\Command\DeleteFolder\DeleteFolderCommandHandler;
use Modules\Tracker\Folder\Application\Command\UpdateFolder\UpdateFolderCommandHandler;
use Modules\Tracker\Folder\Application\Query\GetArchiveForMe\GetArchiveForMeQueryHandler;
use Modules\Tracker\Folder\Application\Query\GetAvailableFoldersForMe\GetAvailableFoldersForMeQueryHandler;
use Modules\Tracker\Folder\Application\Query\GetFolder\GetFolderQueryHandler;
use Modules\Tracker\Folder\Application\Query\GetSharedFoldersForMe\GetSharedFoldersForMeQueryHandler;
use Modules\Tracker\Folder\Application\Query\GetWorkspaceFoldersForMe\GetWorkspaceFoldersForMeQueryHandler;
use Modules\Tracker\Folder\Application\Query\SearchFolders\SearchFoldersQueryHandler;
use Modules\Tracker\Folder\Domain\Repository\FolderRepositoryInterface;
use Modules\Tracker\Folder\Infrastructure\Repository\FolderRepository;

class FolderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FolderRepositoryInterface::class, FolderRepository::class);
        $this->app->tag(CreateFolderCommandHandler::class, 'command_handler');
        $this->app->tag(UpdateFolderCommandHandler::class, 'command_handler');
        $this->app->tag(DeleteFolderCommandHandler::class, 'command_handler');

        $this->app->tag(SearchFoldersQueryHandler::class, 'query_handler');
        $this->app->tag(GetArchiveForMeQueryHandler::class, 'query_handler');
        $this->app->tag(GetSharedFoldersForMeQueryHandler::class, 'query_handler');
        $this->app->tag(GetAvailableFoldersForMeQueryHandler::class, 'query_handler');
        $this->app->tag(GetWorkspaceFoldersForMeQueryHandler::class, 'query_handler');
        $this->app->tag(GetFolderQueryHandler::class, 'query_handler');
    }
}
