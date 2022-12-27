<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;

class HttpException extends \Exception
{
    private int $httpCode;

    /**
     * @var array<string, mixed>
     */
    private array $errors;

    /**
     * @param array<string, mixed> $errors
     */
    public function __construct(string $title = '', int $code = 400, int $httpCode = 400, array $errors = [])
    {
        parent::__construct($title, $code, null);
        $this->httpCode = $httpCode;
        $this->errors = $errors;
    }

    public function getResponse(): JsonResponse
    {
        $response = [
            'code' => $this->code,
            'title' => $this->message,
            'status' => 'error',
        ];

        if ($this->errors) {
            $response['errors'] = $this->errors;
        }

        return response()->json(
            $response,
            $this->httpCode,
        );
    }
}
