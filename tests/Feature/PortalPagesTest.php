<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LibrarianController;
use App\Http\Controllers\PatronController;
use App\Models\LibraryBook;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Opens every page of every portal against the demo data, so a broken view
 * or query shows up as a failing test instead of a 500 page in a demo.
 */
class PortalPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function account(string $role): User
    {
        // Start a fresh session, as a real person signing in on their own device would.
        $this->flushSession();

        return User::where('role', $role)->where('status', 'active')->firstOrFail();
    }

    public function test_every_student_page_loads_with_demo_data(): void
    {
        $student = $this->account('student');
        $this->actingAs($student)->get(route('student.dashboard'))->assertOk()->assertSee($student->name);
        foreach (array_keys(PatronController::STUDENT_SECTIONS) as $section) {
            $this->actingAs($student)->get(route('student.section', $section))->assertOk();
        }
        $this->actingAs($student)->get(route('books.catalog', ['q' => 'clean', 'availability' => 'available', 'sort' => 'title']))->assertOk()->assertSee('Clean Code');
        $this->actingAs($student)->get(route('books.catalog', ['selected' => LibraryBook::first()->id, 'page' => 2]))->assertOk();
        $this->actingAs($student)->get(route('student.section', ['history', 'export' => 'csv']))->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_every_faculty_page_loads_with_demo_data(): void
    {
        $faculty = $this->account('faculty');
        $this->actingAs($faculty)->get(route('faculty.dashboard'))->assertOk();
        foreach (array_keys(PatronController::FACULTY_SECTIONS) as $section) {
            $this->actingAs($faculty)->get(route('staff.section', $section))->assertOk();
        }
        $this->actingAs($faculty)->get(route('books.catalog'))->assertOk();
    }

    public function test_every_librarian_page_loads_for_each_staff_role(): void
    {
        $book = LibraryBook::first();
        foreach (['librarian', 'admin', 'superadmin'] as $role) {
            $staff = $this->account($role);
            $this->actingAs($staff)->get(route('librarian.dashboard'))->assertOk();
            foreach (array_keys(LibrarianController::PAGES) as $section) {
                $this->actingAs($staff)->get(route('librarian.section', $section))->assertOk();
            }
            $this->actingAs($staff)->get(route('librarian.books.edit', $book))->assertOk()->assertSee($book->title);
            $this->actingAs($staff)->get(route('management.accounts.index', ['status' => 'pending']))->assertOk()->assertSee('Ana Reyes');
            $this->actingAs($staff)->get(route('management.data.index'))->assertOk();
        }
    }

    public function test_every_administrator_and_super_administrator_page_loads(): void
    {
        $admin = $this->account('admin');
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        foreach (array_keys(AdminController::ADMIN_PAGES) as $section) {
            $this->actingAs($admin)->get(route('admin.section', $section))->assertOk();
        }

        $super = $this->account('superadmin');
        $this->actingAs($super)->get(route('superadmin.dashboard'))->assertOk()->assertSee($super->name);
        foreach (array_keys(AdminController::SUPER_PAGES) as $section) {
            $this->actingAs($super)->get(route('superadmin.section', $section))->assertOk();
        }
        foreach (['users', 'approvals', 'librarians', 'library-staff', 'data-export', 'announcements', 'circulation'] as $section) {
            $this->actingAs($super)->get(route('superadmin.section', $section))->assertRedirect();
        }
    }

    public function test_demo_dates_are_relative_to_today(): void
    {
        $student = $this->account('student');
        $soonest = $student->loans()->active()->orderBy('due_at')->first();

        $this->assertSame(2, $soonest->daysRemaining());
        $this->actingAs($student)->get(route('student.dashboard'))->assertSee($soonest->due_at->format('M d, Y'))->assertDontSee('2024');
    }
}
