<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeQueryString
{
    /**
     * Search and filter links only use plain text values, so tampered values
     * such as ?q[]=x are dropped instead of reaching views as arrays.
     */
    public function handle(Request $request, Closure $next): Response
    {
        foreach ($request->query->all() as $key => $value) {
            if (! is_scalar($value)) {
                $request->query->remove($key);
            }
        }

        return $next($request);
    }
}
