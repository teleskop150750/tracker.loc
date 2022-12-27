<?php

declare(strict_types=1);

namespace Modules\Tracker\Folder\Domain\Repository;

use App\Exceptions\HttpException;

class FolderNotFoundException extends HttpException
{
}
