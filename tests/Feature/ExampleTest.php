<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_select_login_and_reach_its_dashboard(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Super Admin');

        $this->post('/login', [
            'role' => 'superadmin',
            'identifier' => 'SYS-ADMIN-001',
            'password' => 'secure-password',
        ])->assertRedirect(route('superadmin.dashboard'));

        $this->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('Super Admin Dashboard');
    }

    public function test_super_admin_registration_uses_its_own_account_type(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Super Admin');

        $this->post('/register', [
            'patron_role' => 'superadmin',
            'last_name' => 'Santos',
            'first_name' => 'Maria',
            'middle_initial' => 'R',
            'patron_id' => 'SYS-ADMIN-001',
            'program_or_department' => 'System Owner',
            'email' => 'admin@clcst.edu.ph',
            'mobile_number' => '9171234567',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'privacy_consent' => '1',
        ])->assertRedirect(route('login'))
            ->assertSessionMissing('portal_role');
    }

    public function test_logout_blocks_browser_history_access_for_every_dashboard(): void
    {
        $dashboards = [
            'student' => 'student.dashboard',
            'faculty' => 'faculty.dashboard',
            'staff' => 'librarian.dashboard',
            'superadmin' => 'superadmin.dashboard',
        ];

        foreach ($dashboards as $role => $dashboardRoute) {
            $this->post('/login', [
                'role' => $role,
                'identifier' => strtoupper($role).'-TEST-001',
                'password' => 'secure-password',
            ])->assertRedirect();

            $dashboard = $this->get(route($dashboardRoute));
            $dashboard->assertOk();
            $this->assertStringContainsString('no-store', (string) $dashboard->headers->get('Cache-Control'));

            $this->post(route('logout'))
                ->assertRedirect(route('login'))
                ->assertSessionMissing('portal_role');

            $this->get(route($dashboardRoute))
                ->assertRedirect(route('login'));
        }
    }

    public function test_roles_cannot_open_another_roles_dashboard(): void
    {
        $this->post('/login', [
            'role' => 'student',
            'identifier' => '2024-10492-OL',
            'password' => 'secure-password',
        ]);

        $this->get(route('faculty.dashboard'))->assertForbidden();
        $this->get(route('librarian.dashboard'))->assertForbidden();
        $this->get(route('superadmin.dashboard'))->assertForbidden();
    }
}
