<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Jwt;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates API requests with "Authorization: Bearer <JWT>" and, optionally, limits them to roles:
 * ->middleware('jwt:admin,superadmin'). The role is always re-read from the database.
 */
class AuthenticateJwt
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $payload = ($token = $request->bearerToken()) ? Jwt::verify($token) : null;
        $user = $payload ? User::find($payload['sub'] ?? null) : null;

        if (! $user || ! $user->isActive()) {
            return response()->json(['message' => 'Missing, invalid or expired token.'], 401, ['WWW-Authenticate' => 'Bearer']);
        }
        if ($roles !== [] && ! $user->hasAnyRole($roles)) {
            return response()->json(['message' => 'Your account does not have permission to use this endpoint.'], 403);
        }

        Auth::setUser($user);
        $request->attributes->set('jwt', $payload);

        return $next($request);
    }
}
