<?php

namespace Tests\Feature;

use App\Models\LibraryBook;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use App\Services\CirculationService;
use App\Support\Jwt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Covers the problems found in the final audit before the professor's review.
 */
class FinalAuditTest extends TestCase
{
    use RefreshDatabase;

    private function book(int $copies = 1, string $title = 'Final Audit Book', string $isbn = '9782222222222'): LibraryBook
    {
        $book = LibraryBook::create([
            'title' => $title, 'author' => 'Author', 'isbn' => $isbn, 'category' => 'Testing',
            'status' => 'active', 'loan_policy' => 'circulating',
        ]);
        $book->addCopies($copies);

        return $book;
    }

    public function test_a_mistyped_date_in_the_audit_export_link_is_not_a_server_error(): void
    {
        $librarian = User::factory()->role('librarian')->create();

        $this->actingAs($librarian)->get('/management/audit-logs/export?from=abc')
            ->assertRedirect()->assertSessionHasErrors('from');
        $this->actingAs($librarian)->get('/management/audit-logs/export?from=2026-01-01&to=2026-12-31')->assertOk();
    }

    public function test_a_staff_password_reset_ends_api_access_with_the_old_token(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $token = Jwt::issueFor($student);

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk();
        $this->app['auth']->forgetGuards();

        $this->actingAs($librarian)->post(route('management.accounts.reset-password', $student))->assertRedirect();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->withToken(Jwt::issueFor($student->fresh()))->getJson('/api/v1/auth/me')->assertOk();
    }

    public function test_a_password_change_ends_api_access_with_the_old_token(): void
    {
        $student = User::factory()->role('student')->create(['password' => 'OldPassword123']);
        $token = Jwt::issueFor($student);

        $this->actingAs($student)->put(route('profile.password'), [
            'current_password' => 'OldPassword123',
            'password' => 'NewPassword456',
            'password_confirmation' => 'NewPassword456',
        ])->assertSessionHasNoErrors();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
    }

    public function test_a_patron_in_line_cannot_take_a_copy_held_for_someone_elses_pickup(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $first = User::factory()->role('student')->create();
        $second = User::factory()->role('student')->create();
        $book = $this->book(1);
        $circulation = app(CirculationService::class);

        $held = $circulation->reserve($first, $book);
        $waiting = $circulation->reserve($second, $book);
        $circulation->markReady($librarian, $held);

        $this->actingAs($librarian)->post(route('librarian.reservations.issue', $waiting))
            ->assertSessionHasErrors('circulation');
        $this->assertSame(0, Loan::count());

        // The patron whose pickup is ready still gets the book.
        $this->actingAs($librarian)->post(route('librarian.reservations.issue', $held))->assertSessionHasNoErrors();
        $this->assertSame($first->id, Loan::first()->user_id);
        $this->assertSame('fulfilled', $held->fresh()->status);
        $this->assertSame('pending', $waiting->fresh()->status);
    }

    public function test_an_overdue_fine_for_a_book_with_a_very_long_title_is_saved(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $book = $this->book(1, str_repeat('A very long conference proceedings title ', 6));
        $loan = app(CirculationService::class)->checkout($librarian, $student, $book->copies()->first());
        $loan->update(['due_at' => now()->subDays(4)->endOfDay()]);

        $fine = app(CirculationService::class)->checkin($librarian, $loan);

        $this->assertNotNull($fine);
        $this->assertLessThanOrEqual(150, mb_strlen($fine->reason));
        $this->assertStringStartsWith('Overdue return: A very long', $fine->reason);
    }

    public function test_archiving_a_title_closes_its_waiting_list(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $book = $this->book(1);
        $reservation = app(CirculationService::class)->reserve($student, $book);

        $this->actingAs($librarian)->put(route('librarian.books.update', $book), [
            'title' => $book->title, 'author' => $book->author, 'isbn' => $book->isbn,
            'category' => $book->category, 'loan_policy' => 'circulating', 'status' => 'archived',
        ])->assertSessionHasNoErrors()->assertSessionHas('status');

        $this->assertSame('cancelled', $reservation->fresh()->status);
        $this->assertSame(0, Reservation::open()->count());
    }

    public function test_a_book_import_cannot_archive_a_title_with_copies_on_loan(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $book = $this->book(2);
        app(CirculationService::class)->checkout($librarian, $student, $book->copies()->first());

        $csv = "title,author,isbn,call_number,category,publication_year,total_copies,available_copies,shelf_location,status\n"
            ."{$book->title},Author,{$book->isbn},,Testing,,2,1,,archived\n";
        $file = UploadedFile::fake()->createWithContent('books.csv', $csv);

        $this->actingAs($librarian)->post(route('management.data.import', 'books'), ['csv_file' => $file])
            ->assertSessionHasErrors('csv_file');
        $this->assertSame('active', $book->fresh()->status);
    }
}
