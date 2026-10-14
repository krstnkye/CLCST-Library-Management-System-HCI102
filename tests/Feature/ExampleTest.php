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
        ])->assertRedirect(route('superadmin.dashboard'));
    }
}
