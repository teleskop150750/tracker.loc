<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\HttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\Domain\Security\UserFetcherInterface;

class Authenticate
{
    private UserFetcherInterface $userFetcher;

    public function __construct(UserFetcherInterface $userFetcher)
    {
        $this->userFetcher = $userFetcher;
    }

    /**
     * @return JsonResponse|mixed
     *
     * @throws HttpException
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        if (!$this->userFetcher->getAuthUserOrNull()) {
            throw new HttpException('Не авторизован', 401, 401);
        }

        return $next($request);
    }
}
