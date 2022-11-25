<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class ValidateSignature
{
    public function handle(Request $request, \Closure $next, $relative = null)
    {
        if ($request->hasValidSignature('relative' !== $relative)) {
            return $next($request);
        }

        throw new InvalidSignatureException();
    }
}
