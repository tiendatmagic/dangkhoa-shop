<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JwtCookieToAuthHeader
{
    public function handle(Request $request, Closure $next)
    {
        $hasAuthHeader = $request->headers->has('Authorization');

        if (! $hasAuthHeader) {
            $accessToken = $request->cookie('dangkhoa_access')
                ?? $request->cookie('dangkhoa-token')
                ?? $request->cookie('access_token');

            if (is_string($accessToken) && $accessToken !== '') {
                $request->headers->set('Authorization', 'Bearer ' . $accessToken);
            }
        }

        return $next($request);
    }
}
