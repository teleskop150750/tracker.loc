<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Command\TaskAddFile;

use Modules\Shared\Application\Command\CommandResponseInterface;
use Illuminate\Http\Client\Response;

class TaskAddFileResponse implements CommandResponseInterface
{
    public function __construct(
        public readonly Response $response,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createFromResponse(Response $response): static
    {
        return new static($response);
    }

    public function getReponse(): Response
    {
        return $this->response;
    }
}
