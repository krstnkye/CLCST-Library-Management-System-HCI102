<?php

namespace Tests\Feature;

use App\Models\LibraryBook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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

        // The picked account type is only a hint: the saved role decides the portal.
        $this->post(route('login.authenticate'), [
            'role' => 'superadmin',
            'identifier' => $user->email,
            'password' => 'SecurePass2026',
        ])->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('superadmin.dashboard'))->assertForbidden();
    }

    public function test_privileged_account_fields_are_not_mass_assignable(): void
    {
        $user = new User;

        $this->assertFalse($user->isFillable('role'));
        $this->assertFalse($user->isFillable('status'));
        $this->assertFalse($user->isFillable('approved_by'));
        $this->assertFalse($user->isFillable('last_login_at'));
    }

    public function test_login_always_redirects_to_the_authenticated_users_own_portal(): void
    {
        $student = User::factory()->role('student')->create(['password' => Hash::make('SecurePass2026')]);

        $this->get(route('librarian.dashboard'))->assertRedirect(route('login'));
        $this->post(route('login.authenticate'), [
            'role' => 'student',
            'identifier' => $student->email,
            'password' => 'SecurePass2026',
        ])->assertRedirect(route('student.dashboard'));
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

    public function test_registration_page_offers_only_self_service_roles_and_secure_upload_form(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk()
            ->assertSee('Create your CLCST library account')
            ->assertSee('value="student"', false)
            ->assertSee('value="faculty"', false)
            ->assertSee('value="librarian"', false)
            ->assertDontSee('value="admin"', false)
            ->assertDontSee('value="superadmin"', false)
            ->assertSee('@clcst.edu.ph')
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('id="password-match-message"', false)
            ->assertSee('name="privacy_consent"', false);
    }

    public function test_login_page_exposes_the_real_role_based_sign_in_form(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertSee('Unified Academic Access Gateway')
            ->assertSee('data-role="student"', false)
            ->assertSee('data-role="faculty"', false)
            ->assertSee('data-role="librarian"', false)
            ->assertSee('data-role="admin"', false)
            ->assertSee('data-role="superadmin"', false)
            ->assertSee('name="role"', false)
            ->assertSee('name="identifier"', false)
            ->assertSee('name="password"', false)
            ->assertSee(route('login.authenticate'), false);
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

    public function test_registration_normalizes_identifiers_before_checking_for_duplicates(): void
    {
        Storage::fake('local');
        User::factory()->create(['email' => 'existing@clcst.edu.ph', 'patron_id' => 'STU-EXISTING']);

        $this->post(route('register.store'), [
            'patron_role' => 'student',
            'first_name' => 'Test',
            'last_name' => 'User',
            'patron_id' => 'stu-new-001',
            'program_or_department' => 'Computer Studies',
            'email' => 'EXISTING@CLCST.EDU.PH',
            'mobile_number' => '09171234567',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'credential_file' => UploadedFile::fake()->createWithContent('school-id.pdf', '%PDF-1.4 test document'),
            'privacy_consent' => '1',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
        Storage::disk('local')->assertDirectoryEmpty('credentials');
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

    public function test_bulk_book_import_rejects_invalid_ranges_and_statuses_atomically(): void
    {
        $admin = User::factory()->role('admin')->create();
        $csv = implode("\n", [
            'title,author,isbn,call_number,category,publication_year,total_copies,available_copies,shelf_location,status',
            'Valid Book,Valid Author,9780000000100,QA1,Reference,2024,2,2,Stacks A1,active',
            'Invalid Book,Valid Author,9780000000101,QA2,Reference,3026,1,2,Stacks A2,unknown',
        ]);

        $this->actingAs($admin)->post(route('management.data.import', 'books'), [
            'csv_file' => UploadedFile::fake()->createWithContent('books.csv', $csv),
        ])->assertSessionHasErrors('csv_file');

        $this->assertDatabaseMissing('library_books', ['isbn' => '9780000000100']);
        $this->assertDatabaseMissing('library_books', ['isbn' => '9780000000101']);
    }

    public function test_librarians_cannot_import_or_export_user_accounts(): void
    {
        $librarian = User::factory()->role('librarian')->create();

        $this->actingAs($librarian)->get(route('management.data.export', 'users'))->assertForbidden();
        $this->actingAs($librarian)->post(route('management.data.import', 'users'), [
            'csv_file' => UploadedFile::fake()->create('users.csv', 1, 'text/csv'),
        ])->assertForbidden();
    }

    public function test_administrators_cannot_import_or_modify_privileged_accounts(): void
    {
        $admin = User::factory()->role('admin')->create();
        $superadmin = User::factory()->role('superadmin')->create(['email' => 'owner@clcst.edu.ph']);
        $headers = 'patron_id,name,email,role,status,program_or_department,mobile_number';

        $createCsv = implode("\n", [
            $headers,
            'SA-NEW,New Owner,new-owner@clcst.edu.ph,superadmin,active,Administration,09170000000',
        ]);
        $this->actingAs($admin)->post(route('management.data.import', 'users'), [
            'csv_file' => UploadedFile::fake()->createWithContent('users.csv', $createCsv),
        ])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseMissing('users', ['email' => 'new-owner@clcst.edu.ph']);

        $modifyCsv = implode("\n", [
            $headers,
            "{$superadmin->patron_id},{$superadmin->name},{$superadmin->email},student,active,Administration,09170000000",
        ]);
        $this->actingAs($admin)->post(route('management.data.import', 'users'), [
            'csv_file' => UploadedFile::fake()->createWithContent('users.csv', $modifyCsv),
        ])->assertSessionHasErrors('csv_file');
        $this->assertDatabaseHas('users', ['id' => $superadmin->id, 'role' => 'superadmin']);
    }

    public function test_user_exports_follow_the_account_role_hierarchy(): void
    {
        $admin = User::factory()->role('admin')->create(['name' => 'Current Administrator']);
        User::factory()->role('student')->create(['name' => 'Visible Student']);
        User::factory()->role('librarian')->create(['name' => 'Visible Librarian']);
        User::factory()->role('superadmin')->create(['name' => 'Hidden Super Administrator']);

        $response = $this->actingAs($admin)->get(route('management.data.export', 'users'));

        $response->assertOk()->assertDownload();
        $csv = $response->streamedContent();
        $this->assertStringContainsString('Visible Student', $csv);
        $this->assertStringContainsString('Visible Librarian', $csv);
        $this->assertStringNotContainsString('Current Administrator', $csv);
        $this->assertStringNotContainsString('Hidden Super Administrator', $csv);
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

    public function test_suspending_an_account_revokes_its_sessions_and_remember_token(): void
    {
        $administrator = User::factory()->role('admin')->create();
        $student = User::factory()->role('student')->create();
        $oldToken = $student->remember_token;
        DB::table('sessions')->insert([
            'id' => 'student-session',
            'user_id' => $student->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'test',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($administrator)
            ->post(route('management.accounts.suspend', $student))
            ->assertRedirect();

        $student->refresh();
        $this->assertSame('suspended', $student->status);
        $this->assertNotSame($oldToken, $student->remember_token);
        $this->assertDatabaseMissing('sessions', ['user_id' => $student->id]);
    }

    public function test_security_headers_are_sent_on_web_responses(): void
    {
        $response = $this->get(route('login'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('X-DNS-Prefetch-Control', 'off')
            ->assertHeader('Content-Security-Policy');
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
        foreach (['users', 'approvals', 'books', 'circulation', 'reports', 'audit-logs', 'profile'] as $section) {
            $this->actingAs($admin)->get(route('admin.section', $section))->assertOk();
        }
        $this->actingAs($admin)->get(route('admin.section', 'announcements'))
            ->assertRedirect(route('librarian.section', 'announcements'));
    }
}
