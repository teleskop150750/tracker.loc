<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\DownloadFile;

use Modules\Shared\Application\Query\QueryInterface;

class DownloadFileQuery implements QueryInterface
{
    public function __construct(
        public readonly string $fileId,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromArray(array $data): static
    {
        return new static(
            $data['fileId'],
        );
    }
}
