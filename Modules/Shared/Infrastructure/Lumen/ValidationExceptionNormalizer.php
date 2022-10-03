<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Lumen;

use App\Support\Arr;
use Illuminate\Contracts\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ValidationExceptionNormalizer
{
    private MessageBag $errors;

    private function __construct(MessageBag $errors)
    {
        $this->errors = $errors;
    }

    public static function make(ValidationException $exception): static
    {
        return new static($exception->validator->errors());
    }

    public function getResponse(): JsonResponse
    {
        return new JsonResponse($this->responseData());
    }

    /**
     * Создайте сводку сообщений об ошибках из ошибок проверки.
     */
    protected function summarize(): string
    {
        $messages = $this->errors->all();

        if (!\count($messages) || !\is_string($messages[0])) {
            return 'The given data was invalid';
        }

        $message = Arr::shift($messages);

        if ($count = \count($messages)) {
            $pluralized = 1 === $count ? 'error' : 'errors';

            $message .= ' '."(и еще {$count} {$pluralized})";
        }

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    protected function responseData(): array
    {
        return [
            'success' => false,
            'message' => $this->summarize(),
            'errors' => $this->errors->getMessages(),
        ];
    }
}
