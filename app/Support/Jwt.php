<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Minimal JSON Web Token (RFC 7519) support using HS256, for the library's JSON API.
 * The website itself keeps using encrypted sessions; tokens are only for API clients.
 */
class Jwt
{
    private const ALGORITHM = 'HS256';

    /** Issue a signed token for the given claims. Adds iat, nbf, exp, iss and a unique jti. */
    public static function issue(array $claims, ?int $ttlMinutes = null): string
    {
        $now = time();
        $payload = array_merge([
            'iss' => config('app.url'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + 60 * ($ttlMinutes ?? self::ttl()),
            'jti' => (string) Str::uuid(),
        ], $claims);

        $segments = [
            self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => self::ALGORITHM])),
            self::base64UrlEncode(json_encode($payload)),
        ];
        $segments[] = self::base64UrlEncode(self::sign(implode('.', $segments)));

        return implode('.', $segments);
    }

    /** Verify the signature, algorithm, time claims and revocation; return the payload or null. */
    public static function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$header64, $payload64, $signature64] = $parts;

        $header = json_decode(self::base64UrlDecode($header64), true);
        if (! is_array($header) || ($header['alg'] ?? null) !== self::ALGORITHM) {
            return null; // rejects "alg": "none" and algorithm-swapping tokens
        }

        $expected = self::sign($header64.'.'.$payload64);
        if (! hash_equals($expected, self::base64UrlDecode($signature64))) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payload64), true);
        $now = time();
        if (! is_array($payload) || ($payload['exp'] ?? 0) < $now || ($payload['nbf'] ?? 0) > $now + 30) {
            return null;
        }
        if (isset($payload['jti']) && Cache::has('jwt-revoked:'.$payload['jti'])) {
            return null;
        }

        return $payload;
    }

    /** Revoke a token until it would have expired anyway (used by API logout). */
    public static function revoke(array $payload): void
    {
        if (isset($payload['jti'])) {
            Cache::put('jwt-revoked:'.$payload['jti'], true, max(1, ($payload['exp'] ?? time()) - time()));
        }
    }

    public static function ttl(): int
    {
        return (int) config('library.jwt_ttl_minutes', 60);
    }

    private static function sign(string $data): string
    {
        $key = (string) config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }

        // Derive a separate key so the JWT secret is not the raw encryption key.
        return hash_hmac('sha256', $data, hash_hmac('sha256', 'clcst-jwt-v1', $key, true), true);
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/').str_repeat('=', (4 - strlen($data) % 4) % 4), true);
    }
}
