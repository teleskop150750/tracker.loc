<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Lumen;

use Exception;
use Illuminate\Contracts\Validation\Validator;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\JsonResponse;

// class ValidationException extends Exception
// {
//    public Validator $validator;
//
//    public JsonResponse $response;
//
//    public function __construct(Validator $validator)
//    {
//        parent::__construct(static::summarize($validator));
//
//        $this->validator = $validator;
//        $this->response = $this->buildFailedValidationResponse($this->formatValidationErrors());
//    }
//
//    /**
//     * @return array<string, mixed>
//     */
//    public function errors(): array
//    {
//        return $this->validator->errors()->messages();
//    }
//
//    public function getResponse(): JsonResponse
//    {
//        return $this->response;
//    }
//
//    /**
//     * @param array<string, mixed> $errors
//     */
//    protected function buildFailedValidationResponse(array $errors): JsonResponse
//    {
//        return new JsonResponse($errors);
//    }
//
//    #[ArrayShape(['success' => 'false', 'message' => 'string', 'errors' => 'mixed'])]
//    protected function formatValidationErrors(): array
//    {
//        return [
//            'success' => false,
//            'message' => self::summarize($this->validator),
//            'errors' => $this->errors(),
//        ];
//    }
//
//    /**
//     * Создайте сводку сообщений об ошибках из ошибок проверки.
//     */
//    protected static function summarize(Validator $validator): string
//    {
//        $messages = $validator->errors()->all();
//
//        if (!\count($messages) || !\is_string($messages[0])) {
//            return 'The given data was invalid';
//        }
//
//        $message = array_shift($messages);
//
//        if ($count = \count($messages)) {
//            $pluralized = 1 === $count ? 'error' : 'errors';
//
//            $message .= ' '."(and {$count} more {$pluralized})";
//        }
//
//        return $message;
//    }
// }
