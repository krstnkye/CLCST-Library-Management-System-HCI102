<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

/*
| JSON API secured with JSON Web Tokens (JWT). All routes are prefixed with /api/v1.
| 1. POST /api/v1/auth/login with role, identifier and password returns an access_token.
| 2. Send it on every other request as "Authorization: Bearer <token>".
*/
Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [ApiController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware('jwt')->group(function (): void {
        Route::get('/auth/me', [ApiController::class, 'me']);
        Route::post('/auth/logout', [ApiController::class, 'logout']);
        Route::get('/books', [ApiController::class, 'books']);
    });

    Route::get('/my/loans', [ApiController::class, 'myLoans'])->middleware('jwt:student,faculty');
    Route::get('/audit-logs', [ApiController::class, 'auditLogs'])->middleware('jwt:admin,superadmin');
});
