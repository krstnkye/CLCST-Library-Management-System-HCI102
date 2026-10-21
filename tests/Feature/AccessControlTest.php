<?php

namespace Tests\Feature;

use App\Models\LibraryBook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_real_credentials_and_uses_the_saved_role(): void
    {
        $user = User::factory()->role('student')->create([
            'patron_id' => 'STU-001',
            'password' => Hash::make('SecurePass2026'),
        ]);

        $this->post(route('login.authenticate'), [
            'role' => 'student',
            'identifier' => $user->patron_id,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('identifier');
        $this->assertGuest();

        $this->post(route('login.authenticate'), [
            'role' => 'student',
            'identifier' => $user->email,
            'password' => 'SecurePass2026',
        ])->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_selecting_a_different_role_cannot_change_account_permissions(): void
    {
        $user = User::factory()->role('student')->create(['password' => Hash::make('SecurePass2026')]);

        $this->post(route('login.authenticate'), [
            'role' => 'superadmin',
            'identifier' => $user->email,
            'password' => 'SecurePass2026',
        ])->assertSessionHasErrors('identifier');

        $this->assertGuest();
    }

    public function test_pending_accounts_cannot_sign_in(): void
    {
        $user = User::factory()->role('faculty')->pending()->create(['password' => Hash::make('SecurePass2026')]);

        $this->post(route('login.authenticate'), [
            'role' => 'faculty',
            'identifier' => $user->email,
            'password' => 'SecurePass2026',
        ])->assertSessionHasErrors('identifier');

        $this->assertGuest();
    }

    public function test_each_role_reaches_only_its_own_dashboard(): void
    {
        $routes = [
            'student' => 'student.dashboard',
            'faculty' => 'faculty.dashboard',
            'librarian' => 'librarian.dashboard',
            'admin' => 'admin.dashboard',
            'superadmin' => 'superadmin.dashboard',
        ];

        foreach ($routes as $role => $route) {
            $user = User::factory()->role($role)->create();
            $this->actingAs($user)->get(route($route))->assertOk();

            foreach ($routes as $otherRole => $otherRoute) {
                if ($otherRole !== $role && ! ($role === 'superadmin' && in_array($otherRole, ['admin', 'librarian'], true)) && ! ($role === 'admin' && $otherRole === 'librarian')) {
                    $this->actingAs($user)->get(route($otherRoute))->assertForbidden();
                }
            }
        }
    }

    public function test_logout_invalidates_access_and_prevents_cached_dashboard_content(): void
    {
        $user = User::factory()->role('student')->create();
        $dashboard = $this->actingAs($user)->get(route('student.dashboard'));
        $dashboard->assertOk();
        $this->assertStringContainsString('no-store', (string) $dashboard->headers->get('Cache-Control'));

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get(route('student.dashboard'))->assertRedirect(route('login'));
    }

    public function test_registration_page_exposes_every_role_and_secure_upload_form(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk()
            ->assertSee('Create your CLCST library account')
            ->assertSee('value="student"', false)
            ->assertSee('value="faculty"', false)
            ->assertSee('value="librarian"', false)
            ->assertSee('value="admin"', false)
            ->assertSee('value="superadmin"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="privacy_consent"', false);
    }

    public function test_registration_creates_a_pending_account_and_keeps_the_user_signed_out(): void
    {
        Storage::fake('local');

        $this->post(route('register.store'), [
            'patron_role' => 'librarian',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'middle_initial' => 'R',
            'patron_id' => 'LIB-NEW-001',
            'program_or_department' => 'Main Library',
            'email' => 'maria.santos@clcst.edu.ph',
            'mobile_number' => '09171234567',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'credential_file' => UploadedFile::fake()->createWithContent('school-id.pdf', '%PDF-1.4 test document'),
            'privacy_consent' => '1',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('users', [
            'email' => 'maria.santos@clcst.edu.ph',
            'role' => 'librarian',
            'status' => 'pending',
        ]);
    }

    public function test_administrators_can_import_and_export_books(): void
    {
        $admin = User::factory()->role('admin')->create();
        $csv = implode("\n", [
            'title,author,isbn,call_number,category,publication_year,total_copies,available_copies,shelf_location,status',
            'Database Systems,Coronel,9780000000001,QA76.9,Computer Science,2022,4,3,Stacks A1,active',
        ]);

        $this->actingAs($admin)->post(route('management.data.import', 'books'), [
            'csv_file' => UploadedFile::fake()->createWithContent('books.csv', $csv),
        ])->assertRedirect();

        $this->assertDatabaseHas('library_books', ['isbn' => '9780000000001', 'available_copies' => 3]);
        $this->actingAs($admin)->get(route('management.data.export', 'books'))
            ->assertOk()
            ->assertDownload();
    }

    public function test_librarians_cannot_import_or_export_user_accounts(): void
    {
        $librarian = User::factory()->role('librarian')->create();

        $this->actingAs($librarian)->get(route('management.data.export', 'users'))->assertForbidden();
        $this->actingAs($librarian)->post(route('management.data.import', 'users'), [
            'csv_file' => UploadedFile::fake()->create('users.csv', 1, 'text/csv'),
        ])->assertForbidden();
    }

    public function test_account_approval_follows_the_role_hierarchy(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->pending()->create();
        $administrator = User::factory()->role('admin')->pending()->create();

        $this->actingAs($librarian)
            ->post(route('management.accounts.approve', $student))
            ->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $student->id, 'status' => 'active']);

        $this->actingAs($librarian)
            ->post(route('management.accounts.approve', $administrator))
            ->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $administrator->id, 'status' => 'pending']);
    }

    public function test_student_sidebar_destinations_are_reachable(): void
    {
        $student = User::factory()->role('student')->create();
        $this->actingAs($student)->get(route('books.catalog'))->assertOk();

        foreach (['borrow-return', 'reservations', 'history', 'fines', 'notifications', 'account', 'support'] as $section) {
            $this->actingAs($student)->get(route('student.section', $section))->assertOk();
        }
    }

    public function test_staff_and_administration_navigation_is_reachable(): void
    {
        $faculty = User::factory()->role('faculty')->create();
        foreach (['circulation', 'reservations', 'history', 'academic', 'notifications', 'profile', 'support'] as $section) {
            $this->actingAs($faculty)->get(route('staff.section', $section))->assertOk();
        }

        $librarian = User::factory()->role('librarian')->create();
        foreach (['books', 'inventory', 'borrow-requests', 'returns', 'overdue', 'reservations', 'reports', 'profile', 'support'] as $section) {
            $this->actingAs($librarian)->get(route('librarian.section', $section))->assertOk();
        }
        $this->actingAs($librarian)->get(route('librarian.section', 'verification'))
            ->assertRedirect(route('management.accounts.index'));

        $admin = User::factory()->role('admin')->create();
        foreach (['users', 'approvals', 'books', 'circulation', 'reports', 'audit-logs', 'announcements', 'profile'] as $section) {
            $this->actingAs($admin)->get(route('admin.section', $section))->assertOk();
        }
    }
}
