<?php

namespace Tests\Feature;

use App\Http\Controllers\AccountManagementController;
use App\Models\AuditLog;
use App\Models\Fine;
use App\Models\LibraryBook;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use App\Support\Jwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Covers the problems found in the November full-system audit.
 */
class AuditFixesTest extends TestCase
{
    use RefreshDatabase;

    private function book(int $copies = 1, string $policy = 'circulating', string $isbn = '9781111111111'): LibraryBook
    {
        $book = LibraryBook::create([
            'title' => 'Audit Book '.$isbn, 'author' => 'Author', 'isbn' => $isbn, 'category' => 'Testing',
            'status' => 'active', 'loan_policy' => $policy,
        ]);
        $book->addCopies($copies);

        return $book;
    }

    public function test_a_suspended_account_can_be_reactivated(): void
    {
        $admin = User::factory()->role('admin')->create();
        $student = User::factory()->role('student')->create();

        $this->actingAs($admin)->post(route('management.accounts.suspend', $student))->assertRedirect();
        $this->assertSame('suspended', $student->fresh()->status);

        $this->actingAs($admin)->post(route('management.accounts.reactivate', $student))->assertSessionHas('status_title', 'Account reactivated');
        $this->assertSame('active', $student->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['event' => 'account.reactivated']);

        // Only suspended accounts, and only roles the reviewer manages.
        $this->actingAs($admin)->post(route('management.accounts.reactivate', $student))->assertStatus(422);
        $librarian = User::factory()->role('librarian')->create();
        $otherAdmin = User::factory()->role('admin')->create(['status' => 'suspended']);
        $this->actingAs($librarian)->post(route('management.accounts.reactivate', $otherAdmin))->assertForbidden();
    }

    public function test_staff_can_reset_a_forgotten_password_for_accounts_they_manage(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $oldHash = $student->password;

        $response = $this->actingAs($librarian)->post(route('management.accounts.reset-password', $student));
        $response->assertSessionHas('status_title', 'Password reset');
        $message = session('status');
        $this->assertMatchesRegularExpression('/[A-Z]{4}-[a-z]{4}-[0-9]{4}/', $message);
        preg_match('/[A-Z]{4}-[a-z]{4}-[0-9]{4}/', $message, $match);

        $student->refresh();
        $this->assertNotSame($oldHash, $student->password);
        $this->assertTrue(Hash::check($match[0], $student->password));
        $this->assertDatabaseHas('audit_logs', ['event' => 'account.password_reset']);
        $this->assertStringNotContainsString($match[0], AuditLog::where('event', 'account.password_reset')->first()->description);

        // The new password signs in.
        $this->post(route('logout'));
        $this->postJson(route('login.authenticate'), ['role' => 'student', 'identifier' => $student->email, 'password' => $match[0]])->assertOk();

        // Librarians cannot reset administrators, and nobody resets their own password here.
        $this->post(route('logout'));
        $this->flushSession();
        $admin = User::factory()->role('admin')->create();
        $this->actingAs($librarian)->post(route('management.accounts.reset-password', $admin))->assertForbidden();
        $superadmin = User::factory()->role('superadmin')->create();
        $this->actingAs($superadmin)->post(route('management.accounts.reset-password', $superadmin))->assertStatus(422);
    }

    public function test_temporary_passwords_meet_the_password_policy(): void
    {
        foreach (range(1, 20) as $ignored) {
            $password = AccountManagementController::temporaryPassword();
            $this->assertMatchesRegularExpression('/[A-Z]/', $password);
            $this->assertMatchesRegularExpression('/[a-z]/', $password);
            $this->assertMatchesRegularExpression('/[0-9]/', $password);
            $this->assertGreaterThanOrEqual(10, strlen($password));
        }
    }

    public function test_changing_the_loan_policy_moves_the_shelf_copies(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $book = $this->book(2, 'reference');
        $this->assertSame(['reference', 'reference'], $book->copies()->pluck('status')->all());

        $data = $book->only(['title', 'author', 'isbn', 'category']) + ['loan_policy' => 'circulating', 'status' => 'active'];
        $this->actingAs($librarian)->put(route('librarian.books.update', $book), $data)->assertSessionHasNoErrors();
        $this->assertSame(['available', 'available'], $book->copies()->pluck('status')->all());
        $this->assertSame(2, $book->fresh()->available_copies);

        $this->actingAs($librarian)->put(route('librarian.books.update', $book), ['loan_policy' => 'reference'] + $data)->assertSessionHasNoErrors();
        $this->assertSame(['reference', 'reference'], $book->copies()->pluck('status')->all());
    }

    public function test_archived_titles_cannot_be_checked_out(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $book = $this->book();
        $book->update(['status' => 'archived']);

        $this->actingAs($librarian)->post(route('librarian.checkout'), ['patron' => $student->patron_id, 'barcode' => $book->copies->first()->barcode])
            ->assertSessionHasErrors('circulation');
        $this->assertSame(0, Loan::count());
    }

    public function test_a_shelf_copy_is_never_held_for_two_patrons(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $book = $this->book(1);
        $first = Reservation::create(['user_id' => User::factory()->role('student')->create()->id, 'library_book_id' => $book->id, 'status' => 'pending']);
        $second = Reservation::create(['user_id' => User::factory()->role('student')->create()->id, 'library_book_id' => $book->id, 'status' => 'pending']);

        $this->actingAs($librarian)->post(route('librarian.reservations.ready', $first))->assertSessionHasNoErrors();
        $this->actingAs($librarian)->post(route('librarian.reservations.ready', $second))
            ->assertSessionHasErrors('circulation')->assertSessionHas('error_title', 'Hold not placed');
        $this->assertSame('pending', $second->fresh()->status);
    }

    public function test_a_closed_reservation_cannot_be_issued(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $book = $this->book(1);
        $reservation = Reservation::create(['user_id' => User::factory()->role('student')->create()->id, 'library_book_id' => $book->id, 'status' => 'cancelled']);

        $this->actingAs($librarian)->post(route('librarian.reservations.issue', $reservation))->assertSessionHasErrors('circulation');
        $this->assertSame(0, Loan::count());
    }

    public function test_settling_a_fine_twice_shows_an_error_instead_of_failing(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $fine = Fine::create(['user_id' => $student->id, 'reason' => 'Late', 'days_late' => 2, 'amount' => 10, 'status' => 'paid']);

        $this->actingAs($librarian)->post(route('librarian.fines.settle', $fine), ['outcome' => 'waived'])
            ->assertSessionHasErrors('circulation')->assertSessionHas('error_title', 'Fine not updated');
    }

    public function test_faculty_have_fines_and_borrowing_policy_pages_and_fine_notices(): void
    {
        $faculty = User::factory()->role('faculty')->create();
        Fine::create(['user_id' => $faculty->id, 'reason' => 'Overdue return: Test', 'days_late' => 3, 'amount' => 15, 'status' => 'outstanding']);

        $this->actingAs($faculty)->get(route('staff.section', 'fines'))->assertOk()->assertSee('Overdue return: Test')->assertSee('Faculty Library Portal');
        $this->actingAs($faculty)->get(route('staff.section', 'circulation-policy'))->assertOk()->assertSee('Faculty Library Portal');
        $this->actingAs($faculty)->get(route('staff.section', 'notifications'))->assertOk()->assertSee('Unpaid fine');

        $student = User::factory()->role('student')->create();
        $this->actingAs($student)->get(route('student.section', 'circulation-policy'))->assertOk()->assertSee('Borrowing Policy');
    }

    public function test_history_csv_neutralizes_formulas_and_is_utf8(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $book = $this->book();
        $book->update(['title' => '=HYPERLINK("http://evil")']);
        $this->actingAs($librarian)->post(route('librarian.checkout'), ['patron' => $student->patron_id, 'barcode' => $book->copies->first()->barcode]);

        $csv = $this->actingAs($student)->get(route('student.section', ['history', 'export' => 'csv']))->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_api_book_search_matches_isbns_without_hyphens_and_hides_archived_titles_from_patrons(): void
    {
        $student = User::factory()->role('student')->create();
        $this->book(1, 'circulating', '978-0-13-235088-4');
        $this->book(1, 'circulating', '978-1-23-456789-0')->update(['status' => 'archived']);
        $token = Jwt::issue(['sub' => $student->id, 'role' => 'student']);

        $this->withToken($token)->getJson('/api/v1/books?q=9780132350884')->assertOk()->assertJsonCount(1, 'data');
        $this->withToken($token)->getJson('/api/v1/books?q=978-0-13')->assertOk()->assertJsonCount(1, 'data');
        $this->withToken($token)->getJson('/api/v1/books?q=9781234567890')->assertOk()->assertJsonCount(0, 'data');
    }
}
