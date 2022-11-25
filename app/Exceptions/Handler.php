<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * This mapping holds exceptions we're interested in and creates a simple configuration that can guide us
     * with formatting how it is rendered.
     *
     * @var array|array[]
     */
    protected array $exceptionMap = [
        NotFoundHttpException::class => [
            'code' => 404,
            'message' => 'Could not find what you were looking for.',
        ],

        MethodNotAllowedHttpException::class => [
            'code' => 405,
            'message' => 'This method is not allowed for this endpoint.',
        ],

        ValidationException::class => [
            'code' => 422,
            'message' => 'Some data failed validation in the request',
        ],

        \InvalidArgumentException::class => [
            'code' => 400,
            'message' => 'You provided some invalid input value',
        ],

        InvalidSignatureException::class => [
            'message' => 'Неверная ссылка',
        ],
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     *
     * @throws \Throwable
     */
    public function render($request, \Throwable $exception)
    {
        return parent::render($request, $exception);
        // $response = $this->formatException($exception);

        // return response()->json($response['date'], $response['status'] ?? 500);
    }

    /**
     * A simple implementation to help us format an exception before we render me.
     */
    protected function formatException(\Throwable $exception): array
    {
        // We get the class name for the exception that was raised
        $exceptionClass = \get_class($exception);

        // we see if we have registered it in the mapping - if it isn't
        // we create an initial structure as an 'Internal Server Error'
        // note that this can always be revised at a later time
        if ($this->exceptionMap[$exceptionClass]) {
            return [
                'status' => 200,
                'date' => [
                    'success' => false,
                    'message' => $exception->getMessage() ?? 'Something went wrong while processing your request',
                ],
            ];
        }

        return [
            'date' => [
                'success' => false,
                'message' => $exception->getMessage(),
            ],
        ];
    }
}
