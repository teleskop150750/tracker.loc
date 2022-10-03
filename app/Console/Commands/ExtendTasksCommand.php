<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Modules\Tracker\Task\Application\Command\ExtendTasks\ExtendTasksCommandHandler;

class ExtendTasksCommand extends Command
{
    /**
     * The console command name.
     * */
    protected $name = 'tasks:extend';

    /**
     * The console command description.
     */
    protected $description = 'test controller';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $command = App::make(\Modules\Tracker\Task\Application\Command\ExtendTasks\ExtendTasksCommand::class);
        $handler = App::make(ExtendTasksCommandHandler::class);
        $handler($command);
    }
}
