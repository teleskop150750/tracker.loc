<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @throws \Exception
     */
    public function report(\Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     *
     * @throws \Throwable
     */
    public function render($request, \Throwable $exception): JsonResponse|\Illuminate\Http\Response|Response
    {
        if ($request->wantsJson()) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'code' => 422,
                    'status' => 'error',
                    'title' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof \App\Exceptions\HttpException) {
                return $exception->getResponse();
            }

            return response()->json([
                'code' => 500,
                'status' => 'error',
                'title' => $exception->getMessage(),
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
