<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\DownloadFile;

use Modules\Shared\Application\Query\QueryResponseInterface;

class DownloadFileResponse implements QueryResponseInterface
{
    public function __construct(
        private readonly string $uuid,
        private readonly string $path,
        private readonly string $originName,
    ) {
    }

    public static function fromArray(array $file): self
    {
        return new self(
            $file['uuid'],
            $file['path'],
            $file['originName'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->uuid,
            'path' => $this->path,
            'originName' => $this->originName,
        ];
    }
}
