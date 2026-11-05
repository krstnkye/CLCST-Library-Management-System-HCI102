<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\EnsurePortalAccess;
use App\Http\Middleware\NormalizeQueryString;
use App\Http\Middleware\SecurityHeaders;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // A tunnel or reverse proxy on this machine (for example cloudflared) forwards https requests;
        // trusting only local proxies lets links and assets keep the https address.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);
        $middleware->web(append: [NormalizeQueryString::class, SecurityHeaders::class]);
        // Lets a password change sign out the user's other sessions.
        $middleware->authenticateSessions();
        $middleware->alias([
            'portal' => EnsurePortalAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
