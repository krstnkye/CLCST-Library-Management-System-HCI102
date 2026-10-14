<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalAccess
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $role = $request->session()->get('portal_role');

        if (! is_string($role)) {
            return redirect()
                ->route('login')
                ->with('status', 'Please sign in to continue.');
        }

        if ($allowedRoles !== [] && ! in_array($role, $allowedRoles, true)) {
            abort(403, 'Your account does not have permission to open this page.');
        }

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        return $response;
    }
}
