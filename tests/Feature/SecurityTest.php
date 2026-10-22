<?php

namespace Tests\Feature;

use App\Models\LibraryBook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private function registration(array $overrides = []): array
    {
        return array_merge([
            'patron_role' => 'student',
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
            'patron_id' => 'STU-2026-900',
            'program_or_department' => 'BS Computer Science',
            'email' => 'ana.cruz@clcst.edu.ph',
            'mobile_number' => '09171234567',
            'password' => 'SecurePass2026',
            'password_confirmation' => 'SecurePass2026',
            'credential_file' => UploadedFile::fake()->createWithContent('id.pdf', '%PDF-1.4 test'),
            'privacy_consent' => '1',
        ], $overrides);
    }

    public function test_registration_only_accepts_school_emails(): void
    {
        Storage::fake('local');

        foreach (['ana@gmail.com', 'ana@clcst.edu.ph.evil.com', 'ana@student.clcst.com'] as $email) {
            $this->post(route('register.store'), $this->registration(['email' => $email]))->assertSessionHasErrors('email');
        }
        $this->assertDatabaseCount('users', 0);

        $this->post(route('register.store'), $this->registration())->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['email' => 'ana.cruz@clcst.edu.ph', 'status' => 'pending']);
    }

    public function test_sign_in_rejects_non_school_emails_before_checking_the_password(): void
    {
        User::factory()->create(['email' => 'outsider@gmail.com', 'password' => Hash::make('SecurePass2026')]);

        $this->post(route('login.authenticate'), ['identifier' => 'outsider@gmail.com', 'password' => 'SecurePass2026'])
            ->assertSessionHasErrors(['identifier' => 'Use your school-registered email ending in @clcst.edu.ph, or your school ID.']);
        $this->assertGuest();
    }

    public function test_privileged_roles_cannot_be_requested_through_registration(): void
    {
        Storage::fake('local');

        foreach (['admin', 'superadmin'] as $role) {
            $this->post(route('register.store'), $this->registration(['patron_role' => $role]))->assertSessionHasErrors('patron_role');
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_passwords_are_hashed_and_never_shown(): void
    {
        Storage::fake('local');
        $this->post(route('register.store'), $this->registration());

        $user = User::firstOrFail();
        $this->assertNotSame('SecurePass2026', $user->getAttributes()['password']);
        $this->assertTrue(Hash::check('SecurePass2026', $user->password));
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_users_can_change_their_password_only_with_the_current_one(): void
    {
        $user = User::factory()->role('student')->create(['password' => Hash::make('OldPassword2026')]);

        $this->actingAs($user)->put(route('profile.password'), ['current_password' => 'wrong', 'password' => 'NewPassword2026', 'password_confirmation' => 'NewPassword2026'])
            ->assertSessionHasErrors('current_password');
        $this->actingAs($user)->put(route('profile.password'), ['current_password' => 'OldPassword2026', 'password' => 'weak', 'password_confirmation' => 'weak'])
            ->assertSessionHasErrors('password');
        $this->actingAs($user)->put(route('profile.password'), ['current_password' => 'OldPassword2026', 'password' => 'NewPassword2026', 'password_confirmation' => 'NewPassword2026'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NewPassword2026', $user->fresh()->password));
    }

    public function test_suspended_accounts_are_signed_out_on_their_next_request(): void
    {
        $user = User::factory()->role('student')->create();
        $this->actingAs($user)->get(route('student.dashboard'))->assertOk();

        $user->update(['status' => 'suspended']);
        $this->actingAs($user->fresh())->get(route('student.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_user_supplied_text_is_escaped_in_pages(): void
    {
        $student = User::factory()->role('student')->create();
        LibraryBook::create(['title' => '<script>alert(1)</script>', 'author' => 'X', 'isbn' => '9780000000001', 'category' => 'Test', 'status' => 'active', 'loan_policy' => 'circulating']);

        $this->actingAs($student)->get(route('books.catalog'))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_search_input_is_treated_as_data_not_sql(): void
    {
        $student = User::factory()->role('student')->create();

        $this->actingAs($student)->get(route('books.catalog', ['q' => "' OR 1=1 --"]))->assertOk()->assertSee('0 titles matched');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_forms_carry_csrf_tokens_and_responses_send_security_headers(): void
    {
        $response = $this->get(route('login'));

        $response->assertSee('name="_token"', false)
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_csv_exports_neutralize_spreadsheet_formulas(): void
    {
        $admin = User::factory()->role('admin')->create();
        LibraryBook::create(['title' => '=HYPERLINK("http://evil")', 'author' => 'X', 'isbn' => '9780000000002', 'category' => 'Test', 'status' => 'active', 'loan_policy' => 'circulating']);

        $csv = $this->actingAs($admin)->get(route('management.data.export', 'books'))->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }
}
