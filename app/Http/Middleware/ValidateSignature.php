<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;

class ValidateSignature
{
    public function handle(Request $request, \Closure $next, $relative = null)
    {
        if ($request->hasValidSignature('relative' !== $relative)) {
            return $next($request);
        }

        return response()->json([
            'code' => 422,
            'status' => 'error',
            'title' => 'Неверная ссылка',
        ], 422);
    }
}
