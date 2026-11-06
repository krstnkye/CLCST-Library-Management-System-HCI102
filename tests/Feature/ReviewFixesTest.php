<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewFixesTest extends TestCase
{
    use RefreshDatabase;

    private const USER_CSV_HEADER = 'patron_id,name,email,role,status,program_or_department,mobile_number';

    public function test_administrators_cannot_create_or_change_super_admins_by_import(): void
    {
        $admin = User::factory()->role('admin')->create();
        $superAdmin = User::factory()->role('superadmin')->create(['email' => 'boss@clcst.edu.ph']);

        foreach (['SYS-1,Boss,boss@clcst.edu.ph,superadmin,suspended,,', 'SYS-2,New Boss,new@clcst.edu.ph,superadmin,active,,'] as $row) {
            $this->actingAs($admin)->post(route('management.data.import', 'users'), [
                'csv_file' => UploadedFile::fake()->createWithContent('users.csv', self::USER_CSV_HEADER."\n".$row),
            ])->assertSessionHasErrors('csv_file');
        }

        $this->assertSame('active', $superAdmin->fresh()->status);
        $this->assertDatabaseMissing('users', ['email' => 'new@clcst.edu.ph']);
    }

    public function test_user_import_rejects_a_school_id_owned_by_another_account(): void
    {
        $admin = User::factory()->role('admin')->create();
        User::factory()->create(['patron_id' => 'STU-100', 'email' => 'first@clcst.edu.ph']);

        $this->actingAs($admin)->post(route('management.data.import', 'users'), [
            'csv_file' => UploadedFile::fake()->createWithContent('users.csv', self::USER_CSV_HEADER."\nstu-100,Second,second@clcst.edu.ph,student,active,,"),
        ])->assertSessionHasErrors('csv_file');
    }

    public function test_registration_treats_email_and_school_id_case_insensitively(): void
    {
        Storage::fake('local');
        User::factory()->create(['email' => 'ana@clcst.edu.ph', 'patron_id' => 'STU-9']);

        $this->post(route('register.store'), [
            'patron_role' => 'student',
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
            'patron_id' => 'stu-9',
            'program_or_department' => 'BS Computer Science',
            'email' => 'Ana@CLCST.edu.ph',
            'mobile_number' => '09171234567',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'credential_file' => UploadedFile::fake()->createWithContent('id.pdf', '%PDF-1.4 test'),
            'privacy_consent' => '1',
        ])->assertSessionHasErrors(['email', 'patron_id']);
    }

    public function test_catalog_ignores_malformed_search_values(): void
    {
        $student = User::factory()->role('student')->create();

        $this->actingAs($student)->get('/books/catalog?q[]=python&sort[]=title')->assertOk();
    }

    public function test_portal_pages_show_the_signed_in_account(): void
    {
        $student = User::factory()->role('student')->create(['name' => 'Ana Cruz', 'patron_id' => 'STU-2026-777']);
        foreach (['student.dashboard', 'books.catalog'] as $route) {
            $this->actingAs($student)->get(route($route))->assertSee('Ana Cruz')->assertDontSee('Juan Dela Cruz');
        }
        $this->actingAs($student)->get(route('student.section', 'account'))->assertSee('STU-2026-777')->assertDontSee('2024-10492-OL');

        $faculty = User::factory()->role('faculty')->create(['name' => 'Liza Reyes']);
        $this->actingAs($faculty)->get(route('faculty.dashboard'))->assertSee('Liza Reyes')->assertDontSee('Roberto');
        $this->actingAs($faculty)->get(route('staff.section', 'profile'))->assertSee('Liza Reyes')->assertDontSee('Roberto');

        $librarian = User::factory()->role('librarian')->create(['name' => 'Rosa Lim']);
        $this->actingAs($librarian)->get(route('librarian.dashboard'))->assertSee('Rosa Lim');
    }

    public function test_reviewers_can_open_uploaded_identity_documents_within_their_role(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('credentials/id.pdf', '%PDF-1.4 test');
        $applicant = User::factory()->role('student')->pending()->create(['credential_path' => 'credentials/id.pdf']);
        $applicantLibrarian = User::factory()->role('librarian')->pending()->create(['credential_path' => 'credentials/id.pdf']);
        $librarian = User::factory()->role('librarian')->create();

        $this->actingAs($librarian)->get(route('management.accounts.index'))->assertOk()->assertSee('View ID');
        $this->actingAs($librarian)->get(route('management.accounts.credential', $applicant))->assertOk();
        $this->actingAs($librarian)->get(route('management.accounts.credential', $applicantLibrarian))->assertForbidden();
    }
}
