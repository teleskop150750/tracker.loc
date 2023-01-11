<?php

declare(strict_types=1);

namespace Modules\Tracker\Task\Application\Query\DownloadFile;

use Illuminate\Http\Client\Response;
use Modules\Shared\Application\Query\QueryResponseInterface;

class DownloadFileResponse implements QueryResponseInterface
{
    public function __construct(
        private readonly Response $response,
    ) {
    }

    public static function fromResponse(Response $response): self
    {
        return new self($response);
    }

    public function toArray(): array
    {
        return [
            'body' => $this->response->body(),
            'status' => $this->response->status(),
            'headers' => $this->response->headers(),
        ];
    }
}
