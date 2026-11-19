<?php

namespace Tests\Feature;

use App\Models\LibraryBook;
use App\Models\Reservation;
use App\Models\User;
use App\Services\CirculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the problems found in the full system audit of November 17.
 */
class SystemAuditTest extends TestCase
{
    use RefreshDatabase;

    private function book(): LibraryBook
    {
        $book = LibraryBook::create([
            'title' => 'System Audit Book', 'author' => 'Author', 'isbn' => '9783333333333', 'category' => 'Testing',
            'status' => 'active', 'loan_policy' => 'circulating',
        ]);
        $book->addCopies(2);

        return $book;
    }

    public function test_opening_a_button_only_address_returns_to_the_portal_instead_of_a_framework_page(): void
    {
        $this->get('/logout')->assertRedirect(route('login'));
        $this->get('/books/1/hold')->assertRedirect(route('login'));

        $student = User::factory()->role('student')->create();
        $this->actingAs($student)->get('/logout')->assertRedirect();
        $this->actingAs($student)->followingRedirects()->get('/books/1/hold')
            ->assertOk()->assertDontSee('Laravel')->assertDontSee('MethodNotAllowedHttpException');
    }

    public function test_error_pages_use_the_library_design_for_every_status_code(): void
    {
        foreach ([400, 405, 413, 422, 502] as $code) {
            $html = view('errors.'.($code >= 500 ? '5xx' : ($code === 405 ? '405' : '4xx')), [
                'exception' => new \Symfony\Component\HttpKernel\Exception\HttpException($code),
            ])->render();
            $this->assertStringContainsString('CLCST School Library', $html);
            $this->assertStringContainsString((string) $code, $html);
            $this->assertStringNotContainsString('Laravel', $html);
        }
    }

    public function test_only_the_403_page_says_the_page_is_outside_the_account(): void
    {
        $student = User::factory()->role('student')->create();

        $this->actingAs($student)->get('/no-such-page')->assertNotFound()->assertDontSee('outside what your account can open');
        $this->actingAs($student)->get('/librarian/dashboard')->assertForbidden()->assertSee('outside what your account can open');
    }

    public function test_a_refused_account_action_shows_a_message_instead_of_an_error_page(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();

        // Approving an account that is already active (for example after a double click or Back).
        $this->actingAs($librarian)->from(route('management.accounts.index'))
            ->post(route('management.accounts.approve', $student))
            ->assertRedirect(route('management.accounts.index'))
            ->assertSessionHasErrors('account');

        $this->actingAs($librarian)->from(route('management.accounts.index'))
            ->post(route('management.accounts.reactivate', $student))
            ->assertSessionHasErrors('account');
    }

    public function test_saving_settings_with_nothing_submitted_shows_a_message(): void
    {
        $super = User::factory()->role('superadmin')->create();

        $this->actingAs($super)->from(route('superadmin.section', 'borrowing-rules'))
            ->put(route('superadmin.settings.update'), [])
            ->assertRedirect(route('superadmin.section', 'borrowing-rules'))
            ->assertSessionHasErrors('settings');
    }

    public function test_a_copy_returned_after_its_title_became_reference_stays_in_the_library(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $book = $this->book();
        $copy = $book->copies()->first();
        $loan = app(CirculationService::class)->checkout($librarian, $student, $copy);

        $this->actingAs($librarian)->put(route('librarian.books.update', $book), [
            'title' => $book->title, 'author' => $book->author, 'isbn' => $book->isbn, 'category' => $book->category,
            'loan_policy' => 'reference', 'status' => 'active',
        ])->assertRedirect();

        app(CirculationService::class)->checkin($librarian, $loan);

        $this->assertSame('reference', $copy->fresh()->status);
        $this->assertSame(0, $book->copies()->where('status', 'available')->count());
    }

    public function test_making_a_title_reference_only_closes_its_waiting_list(): void
    {
        $librarian = User::factory()->role('librarian')->create();
        $student = User::factory()->role('student')->create();
        $book = $this->book();
        $reservation = app(CirculationService::class)->reserve($student, $book);

        $this->actingAs($librarian)->put(route('librarian.books.update', $book), [
            'title' => $book->title, 'author' => $book->author, 'isbn' => $book->isbn, 'category' => $book->category,
            'loan_policy' => 'reference', 'status' => 'active',
        ])->assertSessionHas('status', fn ($status) => str_contains($status, 'reference only'));

        $this->assertSame('cancelled', $reservation->fresh()->status);
        $this->assertSame(0, Reservation::open()->count());
    }

    public function test_responses_do_not_advertise_the_server_software(): void
    {
        $this->get('/')->assertHeaderMissing('X-Powered-By');
    }

    public function test_the_example_settings_keep_developer_error_pages_off(): void
    {
        $this->assertMatchesRegularExpression('/^APP_DEBUG=false$/m', file_get_contents(base_path('.env.example')));
    }
}
