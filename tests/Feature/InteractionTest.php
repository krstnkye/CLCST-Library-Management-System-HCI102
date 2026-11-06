<?php

namespace Tests\Feature;

use App\Models\LibraryBook;
use App\Models\User;
use App\Support\PatronNotifications;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Behaviour behind the pop-ups and buttons: notification read marks, result
 * messages after circulation actions, scanner-friendly search, and the camera
 * permission the scanner needs.
 */
class InteractionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function student(): User
    {
        return User::where('role', 'student')->where('status', 'active')->firstOrFail();
    }

    public function test_patrons_can_mark_all_notifications_read_and_unread(): void
    {
        $student = $this->student();
        $total = PatronNotifications::for($student)->count();
        $this->assertGreaterThan(0, $total);
        $this->assertSame($total, PatronNotifications::unreadCount($student));

        $this->actingAs($student)->postJson(route('notifications.read-all'))->assertOk()->assertJson(['unread' => 0]);
        $this->assertSame(0, PatronNotifications::unreadCount($student));
        $this->actingAs($student)->get(route('student.section', 'notifications'))->assertOk()->assertSee('0</span> unread', false);

        $this->actingAs($student)->postJson(route('notifications.unread-all'))->assertOk()->assertJson(['unread' => $total]);
        $this->assertSame($total, PatronNotifications::unreadCount($student));
    }

    public function test_a_single_notification_can_be_toggled_and_only_its_owner_can_mark_it(): void
    {
        $student = $this->student();
        $key = PatronNotifications::for($student)->first()['key'];

        $this->actingAs($student)->postJson(route('notifications.read', $key))->assertOk();
        $this->assertTrue(PatronNotifications::for($student)->firstWhere('key', $key)['read']);

        $this->actingAs($student)->post(route('notifications.unread', $key))->assertRedirect();
        $this->assertFalse(PatronNotifications::for($student)->firstWhere('key', $key)['read']);

        $this->actingAs($student)->postJson(route('notifications.read', 'loan-overdue-999999'))->assertNotFound();
        $this->flushSession();
        $librarian = User::where('role', 'librarian')->firstOrFail();
        $this->actingAs($librarian)->postJson(route('notifications.read-all'))->assertForbidden();
    }

    public function test_reserving_shows_a_clear_success_or_failure_message(): void
    {
        $student = $this->student();
        $book = LibraryBook::where('loan_policy', '!=', 'reference')->whereDoesntHave('reservations', fn ($q) => $q->where('user_id', $student->id))->firstOrFail();

        $this->actingAs($student)->from(route('books.catalog'))->post(route('books.hold', $book))
            ->assertRedirect(route('books.catalog'))
            ->assertSessionHas('status_title', 'Reservation successful')
            ->assertSessionHas('status_link');

        $this->actingAs($student)->from(route('books.catalog'))->post(route('books.hold', $book))
            ->assertSessionHasErrors('circulation')
            ->assertSessionHas('error_title', 'Reservation not placed');
    }

    public function test_scanned_isbns_without_hyphens_and_advanced_filters_find_books(): void
    {
        $student = $this->student();
        $book = LibraryBook::where('isbn', 'like', '%-%')->firstOrFail();
        $digits = str_replace('-', '', $book->isbn);

        $this->actingAs($student)->get(route('books.catalog', ['q' => $digits]))->assertOk()->assertSee($book->title);
        $this->actingAs($student)->get(route('books.catalog', ['author' => $book->author, 'year_from' => $book->publication_year]))
            ->assertOk()->assertSee($book->title)->assertSee('Author: '.$book->author);
        $this->actingAs($student)->get(route('books.catalog', ['title' => 'no such title anywhere']))->assertOk()->assertSee('No catalog titles found');

        $this->flushSession();
        $librarian = User::where('role', 'librarian')->firstOrFail();
        $this->actingAs($librarian)->get(route('librarian.section', ['books', 'q' => $digits]))->assertOk()->assertSee($book->title);
    }

    public function test_the_camera_is_allowed_for_the_scanner(): void
    {
        $this->get(route('login'))->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
    }
}
