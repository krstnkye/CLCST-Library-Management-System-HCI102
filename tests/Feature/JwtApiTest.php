<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Jwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JwtApiTest extends TestCase
{
    use RefreshDatabase;

    private function login(User $user, string $role, string $password = 'ApiPass2026'): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/auth/login', ['role' => $role, 'identifier' => $user->email, 'password' => $password]);
    }

    public function test_login_returns_a_signed_token_that_opens_protected_endpoints(): void
    {
        $student = User::factory()->create(['password' => 'ApiPass2026']);

        $token = $this->login($student, 'student')->assertOk()
            ->assertJsonPath('token_type', 'Bearer')->assertJsonPath('user.role', 'student')
            ->json('access_token');

        $this->assertCount(3, explode('.', $token));
        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer {$token}"])->assertOk()->assertJsonPath('user.email', $student->email);
        $this->getJson('/api/v1/books', ['Authorization' => "Bearer {$token}"])->assertOk();
        $this->getJson('/api/v1/my/loans', ['Authorization' => "Bearer {$token}"])->assertOk();
    }

    public function test_login_is_strict_about_role_and_password(): void
    {
        $student = User::factory()->create(['password' => 'ApiPass2026']);

        $this->login($student, 'superadmin')->assertUnauthorized();
        $this->login($student, 'student', 'WrongPass2026')->assertUnauthorized();
        $this->postJson('/api/v1/auth/login', ['identifier' => $student->email, 'password' => 'ApiPass2026'])->assertUnprocessable();
    }

    public function test_roles_are_enforced_on_api_endpoints(): void
    {
        $student = User::factory()->create(['password' => 'ApiPass2026']);
        $admin = User::factory()->role('superadmin')->create(['password' => 'ApiPass2026']);
        $studentToken = $this->login($student, 'student')->json('access_token');
        $adminToken = $this->login($admin, 'superadmin')->json('access_token');

        $this->getJson('/api/v1/audit-logs', ['Authorization' => "Bearer {$studentToken}"])->assertForbidden();
        $this->getJson('/api/v1/audit-logs', ['Authorization' => "Bearer {$adminToken}"])->assertOk();
        $this->getJson('/api/v1/my/loans', ['Authorization' => "Bearer {$adminToken}"])->assertForbidden();
    }

    public function test_missing_tampered_expired_and_revoked_tokens_are_rejected(): void
    {
        $student = User::factory()->create(['password' => 'ApiPass2026']);
        $token = $this->login($student, 'student')->json('access_token');

        $this->getJson('/api/v1/auth/me')->assertUnauthorized();

        [$h, $p, $s] = explode('.', $token);
        $forged = rtrim(strtr(base64_encode(json_encode(['sub' => $student->id, 'role' => 'superadmin', 'exp' => time() + 600])), '+/', '-_'), '=');
        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer {$h}.{$forged}.{$s}"])->assertUnauthorized();

        $none = rtrim(strtr(base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'none'])), '+/', '-_'), '=');
        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer {$none}.{$p}."])->assertUnauthorized();

        $expired = Jwt::issue(['sub' => $student->id, 'role' => 'student'], -1);
        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer {$expired}"])->assertUnauthorized();

        $this->postJson('/api/v1/auth/logout', [], ['Authorization' => "Bearer {$token}"])->assertOk();
        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer {$token}"])->assertUnauthorized();
    }

    public function test_suspended_accounts_lose_api_access_immediately(): void
    {
        $student = User::factory()->create(['password' => 'ApiPass2026']);
        $token = $this->login($student, 'student')->json('access_token');
        $student->forceFill(['status' => 'suspended'])->save();

        $this->getJson('/api/v1/auth/me', ['Authorization' => "Bearer {$token}"])->assertUnauthorized();
    }
}
