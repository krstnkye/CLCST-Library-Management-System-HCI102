<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Router;
use Tests\TestCase;

/**
 * Walks every route that is limited to certain roles and checks that each
 * other role is refused, for pages and for actions alike. A new route that
 * forgets its role check, or lists the wrong roles, makes this test fail.
 */
class RoleIsolationTest extends TestCase
{
    use RefreshDatabase;

    private const ROLES = ['student', 'faculty', 'librarian', 'admin', 'superadmin'];

    public function test_every_role_limited_route_refuses_the_other_roles(): void
    {
        $users = collect(self::ROLES)->mapWithKeys(fn ($role) => [$role => User::factory()->role($role)->create()]);
        $checked = 0;

        foreach (Router::getRoutes() as $route) {
            $allowed = $this->allowedRoles($route);
            if ($allowed === null || count($allowed) === count(self::ROLES)) {
                continue;
            }

            $uri = '/'.ltrim(preg_replace('/\{[^}]+\}/', '1', $route->uri()), '/');
            $method = $route->methods()[0];

            foreach (array_diff(self::ROLES, $allowed) as $role) {
                $status = $this->actingAs($users[$role])->call($method, $uri)->getStatusCode();
                $this->assertContains($status, [403, 404], "{$role} reached {$method} {$uri} (status {$status}).");
                $checked++;
            }
        }

        $this->assertGreaterThan(100, $checked);
    }

    public function test_each_portal_is_limited_to_the_expected_roles(): void
    {
        $expected = [
            'student' => ['student'],
            'faculty' => ['faculty'],
            'librarian' => ['librarian', 'admin', 'superadmin'],
            'admin' => ['admin', 'superadmin'],
            'super-admin' => ['superadmin'],
            'management' => ['librarian', 'admin', 'superadmin'],
        ];

        foreach (Router::getRoutes() as $route) {
            $prefix = explode('/', $route->uri())[0];
            if (! isset($expected[$prefix])) {
                continue;
            }
            $this->assertSame($expected[$prefix], $this->allowedRoles($route), "Wrong roles on {$route->uri()}.");
        }
    }

    public function test_signed_out_visitors_cannot_open_any_portal_page(): void
    {
        foreach (['/student/dashboard', '/faculty/dashboard', '/librarian/dashboard', '/admin/dashboard', '/super-admin/dashboard', '/management/accounts', '/books/catalog', '/profile/photo/1'] as $uri) {
            $this->get($uri)->assertRedirect(route('login'));
        }
    }

    private function allowedRoles(Route $route): ?array
    {
        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'portal')) {
                $list = explode(':', $middleware, 2)[1] ?? '';

                return $list === '' ? self::ROLES : explode(',', $list);
            }
        }

        return null;
    }
}
