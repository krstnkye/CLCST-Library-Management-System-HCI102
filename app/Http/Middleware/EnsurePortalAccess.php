<?php

namespace App\Http\Middleware;

use App\Models\Reservation;
use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalAccess
{
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('status', 'Please sign in to continue.');
        }

        if (! $user->isActive()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'identifier' => 'This account is awaiting approval or has been disabled. Contact the library office.',
            ]);
        }

        if ($allowedRoles !== [] && ! $user->hasAnyRole($allowedRoles)) {
            abort(403, 'Your account does not have permission to open this page.');
        }

        // Most installs (a laptop, shared hosting) have no task scheduler running, so uncollected
        // pickup holds are also closed here, at most once every 10 minutes.
        if (Cache::add('library:holds-expiry-check', true, now()->addMinutes(10))) {
            Reservation::expireUncollectedHolds();
        }

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');

        return $response;
    }
}
