<?php

namespace Tests\Feature;

use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\LibraryBook;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CirculationTest extends TestCase
{
    use RefreshDatabase;

    private function book(int $copies = 2, string $policy = 'circulating'): LibraryBook
    {
        static $isbn = 1000000000;
        $book = LibraryBook::create([
            'title' => 'Test Book '.$isbn, 'author' => 'Author', 'isbn' => '978'.$isbn++, 'category' => 'Testing',
            'status' => 'active', 'loan_policy' => $policy,
        ]);
        for ($i = 1; $i <= $copies; $i++) {
            $book->copies()->create(['barcode' => $book->isbn.$i, 'copy_code' => 'TB-00'.$i, 'status' => $policy === 'reference' ? 'reference' : 'available']);
        }
        $book->syncCopyCounts();

        return $book;
    }

    public function test_a_patron_can_reserve_and_cancel_a_book_and_it_survives_logout(): void
    {
        $student = User::factory()->role('student')->create();
        $book = $this->book();

        $this->actingAs($student)->post(route('books.hold', $book))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', ['user_id' => $student->id, 'library_book_id' => $book->id, 'status' => 'pending']);

        $this->post(route('logout'));
        $this->actingAs($student->fresh())->get(route('student.section', 'reservations'))->assertSee($book->title);

        $this->actingAs($student)->post(route('books.hold', $book))->assertSessionHasErrors('circulation');
        $this->actingAs($student)->post(route('reservations.cancel', Reservation::first()))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reservations', ['library_book_id' => $book->id, 'status' => 'cancelled']);
    }

    public function test_patrons_cannot_cancel_someone_elses_reservation(): void
    {
        $owner = User::factory()->role('student')->create();
        $other = User::factory()->role('student')->create();
        $reservation = Reservation::create(['user_id' => $owner->id, 'library_book_id' => $this->book()->id, 'status' => 'pending']);

        $this->actingAs($other)->post(route('reservations.cancel', $reservation))->assertForbidden();
        $this->assertSame('pending', $reservation->fresh()->status);
    }

    public function test_reference_books_cannot_be_reserved_or_lent(): void
    {
        $student = User::factory()->role('student')->create();
        $librarian = User::factory()->role('librarian')->create();
        $book = $this->book(1, 'reference');

        $this->actingAs($student)->post(route('books.hold', $book))->assertSessionHasErrors('circulation');
        $this->flushSession();
        $this->actingAs($librarian)->post(route('librarian.checkout'), ['patron' => $student->patron_id, 'barcode' => $book->copies->first()->barcode])
            ->assertSessionHasErrors('circulation');
        $this->assertSame(0, Loan::count());
    }

    public function test_checkout_renew_and_late_return_follow_the_policy(): void
    {
        $student = User::factory()->role('student')->create();
        $librarian = User::factory()->role('librarian')->create();
        $book = $this->book();
        $copy = $book->copies->first();

        $this->actingAs($librarian)->post(route('librarian.checkout'), ['patron' => $student->email, 'barcode' => $copy->barcode])
            ->assertSessionHasNoErrors();
        $loan = Loan::firstOrFail();
        $this->assertSame('on_loan', $copy->fresh()->status);
        $this->assertSame(1, $book->fresh()->available_copies);
        $this->assertSame(now()->addDays(7)->toDateString(), $loan->due_at->toDateString());

        // The same copy cannot be lent twice.
        $other = User::factory()->role('student')->create();
        $this->actingAs($librarian)->post(route('librarian.checkout'), ['patron' => $other->patron_id, 'barcode' => $copy->barcode])
            ->assertSessionHasErrors('circulation');

        $this->flushSession();
        $this->actingAs($student)->post(route('loans.renew', $loan))->assertSessionHasNoErrors();
        $this->assertSame(now()->addDays(14)->toDateString(), $loan->fresh()->due_at->toDateString());
        $this->actingAs($student)->post(route('loans.renew', $loan))->assertSessionHasErrors('circulation');

        $this->travel(17)->days();
        $this->flushSession();
        $this->actingAs($librarian)->post(route('librarian.checkin'), ['barcode' => $copy->barcode])->assertSessionHasNoErrors();

        $this->assertNotNull($loan->fresh()->returned_at);
        $this->assertSame('available', $copy->fresh()->status);
        $fine = Fine::firstOrFail();
        $this->assertSame(3, $fine->days_late);
        $this->assertSame('15.00', $fine->amount);
        $this->assertSame('outstanding', $fine->status);
    }

    public function test_patrons_with_overdue_books_or_at_the_limit_cannot_borrow(): void
    {
        $student = User::factory()->role('student')->create();
        $librarian = User::factory()->role('librarian')->create();
        $first = $this->book(1);
        $second = $this->book(1);

        Loan::create(['user_id' => $student->id, 'book_copy_id' => $first->copies->first()->id, 'receipt_no' => 'LN-TEST-1', 'borrowed_at' => now()->subDays(10), 'due_at' => now()->subDays(3)]);
        $first->copies->first()->update(['status' => 'on_loan']);

        $this->actingAs($librarian)->post(route('librarian.checkout'), ['patron' => $student->patron_id, 'barcode' => $second->copies->first()->barcode])
            ->assertSessionHasErrors('circulation');

        $this->flushSession();
        $this->actingAs($student)->post(route('loans.renew', Loan::first()))->assertSessionHasErrors('circulation');
    }

    public function test_shelf_copies_are_kept_for_patrons_on_the_waiting_list(): void
    {
        $waiting = User::factory()->role('student')->create();
        $walkIn = User::factory()->role('student')->create();
        $librarian = User::factory()->role('librarian')->create();
        $book = $this->book(1);
        Reservation::create(['user_id' => $waiting->id, 'library_book_id' => $book->id, 'status' => 'pending']);

        $this->actingAs($librarian)->post(route('librarian.checkout'), ['patron' => $walkIn->patron_id, 'barcode' => $book->copies->first()->barcode])
            ->assertSessionHasErrors('circulation');

        $this->actingAs($librarian)->post(route('librarian.reservations.issue', Reservation::first()))->assertSessionHasNoErrors();
        $this->assertSame('fulfilled', Reservation::first()->status);
        $this->assertSame($waiting->id, Loan::first()->user_id);
    }

    public function test_only_staff_settle_fines_and_receipts_are_issued(): void
    {
        $student = User::factory()->role('student')->create();
        $librarian = User::factory()->role('librarian')->create();
        $fine = Fine::create(['user_id' => $student->id, 'reason' => 'Overdue return', 'days_late' => 2, 'amount' => 10, 'status' => 'outstanding']);

        $this->actingAs($student)->post(route('librarian.fines.settle', $fine), ['outcome' => 'paid'])->assertForbidden();
        $this->assertSame('outstanding', $fine->fresh()->status);

        $this->flushSession();
        $this->actingAs($librarian)->post(route('librarian.fines.settle', $fine), ['outcome' => 'paid'])->assertSessionHasNoErrors();
        $this->assertSame('paid', $fine->fresh()->status);
        $this->assertStringStartsWith('OR-', $fine->fresh()->receipt_no);
        $this->actingAs($librarian)->post(route('librarian.fines.settle', $fine), ['outcome' => 'waived'])->assertSessionHasErrors('circulation');
    }

    public function test_librarians_can_add_books_and_mark_copies(): void
    {
        $librarian = User::factory()->role('librarian')->create();

        $this->actingAs($librarian)->post(route('librarian.books.store'), [
            'title' => 'Designing Interfaces', 'author' => 'Jenifer Tidwell', 'isbn' => '978-1492051961',
            'category' => 'Computer Studies & IT', 'loan_policy' => 'circulating', 'copies' => 3,
        ])->assertSessionHasNoErrors();

        $book = LibraryBook::where('isbn', '978-1492051961')->firstOrFail();
        $this->assertSame(3, $book->total_copies);
        $this->assertSame(3, BookCopy::where('library_book_id', $book->id)->distinct()->count('barcode'));

        $this->actingAs($librarian)->post(route('librarian.books.store'), [
            'title' => 'Duplicate', 'author' => 'X', 'isbn' => '978-1492051961', 'category' => 'X', 'loan_policy' => 'circulating', 'copies' => 1,
        ])->assertSessionHasErrors('isbn');

        $copy = $book->copies()->first();
        $this->actingAs($librarian)->patch(route('librarian.copies.update', $copy), ['status' => 'maintenance'])->assertSessionHasNoErrors();
        $this->assertSame(2, $book->fresh()->available_copies);
    }

    public function test_patrons_cannot_open_librarian_tools(): void
    {
        $student = User::factory()->role('student')->create();
        $faculty = User::factory()->role('faculty')->create();

        $this->actingAs($student)->get(route('librarian.dashboard'))->assertForbidden();
        $this->actingAs($student)->post(route('librarian.checkout'), ['patron' => $student->patron_id, 'barcode' => '1'])->assertForbidden();
        $this->actingAs($faculty)->post(route('librarian.books.store'), [])->assertForbidden();
    }

    public function test_faculty_requests_reach_the_librarian_and_replies_come_back(): void
    {
        $faculty = User::factory()->role('faculty')->create();
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();

        $this->actingAs($faculty)->post(route('patron.requests.store'), ['type' => 'course_reserve', 'subject' => 'HCI102 reserve', 'details' => 'Two copies'])
            ->assertSessionHasNoErrors();
        $this->actingAs($student)->post(route('patron.requests.store'), ['type' => 'course_reserve', 'subject' => 'Not allowed'])
            ->assertSessionHasErrors('type');

        $this->flushSession();
        $request = $faculty->libraryRequests()->firstOrFail();
        $this->actingAs($librarian)->patch(route('librarian.requests.update', $request), ['status' => 'approved', 'response' => 'Placed on the reserve shelf.'])
            ->assertSessionHasNoErrors();

        $this->flushSession();
        $this->actingAs($faculty)->get(route('staff.section', 'course-reserves'))->assertSee('Placed on the reserve shelf.');
    }

    public function test_super_admins_can_change_the_policy_and_others_cannot(): void
    {
        $admin = User::factory()->role('admin')->create();
        $super = User::factory()->role('superadmin')->create();

        $this->actingAs($admin)->put(route('superadmin.settings.update'), ['loan_days_student' => 3])->assertForbidden();

        $this->flushSession();
        $this->actingAs($super)->put(route('superadmin.settings.update'), ['loan_days_student' => 999])->assertSessionHasErrors('loan_days_student');
        $this->actingAs($super)->put(route('superadmin.settings.update'), ['loan_days_student' => 10, 'fine_per_day' => 7.5])->assertSessionHasNoErrors();

        $student = User::factory()->role('student')->create();
        $librarian = User::factory()->role('librarian')->create();
        $book = $this->book(1);
        $this->flushSession();
        $this->actingAs($librarian)->post(route('librarian.checkout'), ['patron' => $student->patron_id, 'barcode' => $book->copies->first()->barcode]);
        $this->assertSame(now()->addDays(10)->toDateString(), Loan::firstOrFail()->due_at->toDateString());
    }
}
