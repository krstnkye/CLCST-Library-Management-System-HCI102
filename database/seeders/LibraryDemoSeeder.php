<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\LibraryBook;
use App\Models\LibraryRequest;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo catalog and circulation records. Dates are relative to today so the
 * dashboards always show realistic "due in 2 days" style information.
 */
class LibraryDemoSeeder extends Seeder
{
    private const BOOKS = [
        ['Introduction to Python Programming', 'John Zelle', 'Franklin, Beedle & Associates', '978-1590282755', 'QA76.73.P98 Z45 2021', 'Computer Studies & IT', 'Programming', 2021, '2nd Floor, Shelf 2B-04', 'circulating', 'PY', 5],
        ['Clean Code: A Handbook of Agile Software Craftsmanship', 'Robert C. Martin', 'Addison-Wesley', '978-0132350884', 'QA76.76.C64 M37', 'Computer Studies & IT', 'Software Engineering', 2008, '2nd Floor, Shelf 2B-06', 'circulating', 'CC', 4],
        ['Introduction to Algorithms (4th Edition)', 'Cormen, Leiserson, Rivest, Stein', 'MIT Press', '978-0262046305', 'QA76.6 .I585', 'Computer Studies & IT', 'Algorithms', 2022, '2nd Floor, Shelf 2A-01', 'circulating', 'AL', 3],
        ['Artificial Intelligence: A Modern Approach (4th Edition)', 'Stuart Russell & Peter Norvig', 'Pearson', '978-0134610993', 'Q335 .R86 2020', 'Computer Studies & IT', 'Artificial Intelligence', 2020, '3rd Floor, Shelf 3A-05', 'circulating', 'AI', 2],
        ['Computer Networks: A Systems Approach (6th Edition)', 'Larry L. Peterson & Bruce S. Davie', 'Morgan Kaufmann', '978-0128182000', 'TK5105.5 .P479 2021', 'Engineering', 'Networking', 2021, 'Room 102, Reference Desk', 'reference', 'CN', 2],
        ['Object-Oriented Software Engineering: An Agile Unified Methodology', 'David Kung', 'McGraw-Hill', '978-0073376257', 'QA76.758 .K86', 'Computer Studies & IT', 'Software Engineering', 2014, '2nd Floor, Shelf 2B-06', 'circulating', 'SE', 3],
        ['Database System Concepts (7th Edition)', 'Silberschatz, Korth, Sudarshan', 'McGraw-Hill', '978-0078022159', 'QA76.9.D3 S56', 'Computer Studies & IT', 'Databases', 2019, '2nd Floor, Shelf 2A-03', 'circulating', 'DS', 3],
        ['Operating Systems: Three Easy Pieces', 'Remzi & Andrea Arpaci-Dusseau', 'Arpaci-Dusseau Books', '978-1985086593', 'QA76.76.O63 A77', 'Computer Studies & IT', 'Operating Systems', 2018, '2nd Floor, Shelf 2A-04', 'circulating', 'OS', 2],
        ['Discrete Mathematics and Its Applications', 'Kenneth H. Rosen', 'McGraw-Hill', '978-1259676512', 'QA39.3 .R67', 'General Education', 'Mathematics', 2018, '1st Floor, Shelf 1C-02', 'circulating', 'DM', 3],
        ['Fundamentals of Accounting Principles', 'John J. Wild', 'McGraw-Hill', '978-1260247985', 'HF5636 .W55', 'Business & Accountancy', 'Accounting', 2021, '1st Floor, Shelf 1D-01', 'circulating', 'AC', 2],
        ['Engineering Mechanics: Statics', 'R. C. Hibbeler', 'Pearson', '978-0133918922', 'TA351 .H5', 'Engineering', 'Mechanics', 2016, '3rd Floor, Shelf 3B-02', 'circulating', 'EM', 2],
        ['Human-Computer Interaction (3rd Edition)', 'Alan Dix, Janet Finlay, Gregory Abowd, Russell Beale', 'Pearson', '978-0130461094', 'QA76.9.H85 D59', 'Computer Studies & IT', 'Human-Computer Interaction', 2004, '2nd Floor, Shelf 2C-01', 'circulating', 'HC', 3],
    ];

    public function run(): void
    {
        $books = collect(self::BOOKS)->map(function (array $row, int $index): LibraryBook {
            [$title, $author, $publisher, $isbn, $call, $category, $subject, $year, $shelf, $policy, $prefix, $copies] = $row;
            $book = LibraryBook::updateOrCreate(['isbn' => $isbn], [
                'title' => $title, 'author' => $author, 'publisher' => $publisher, 'call_number' => $call,
                'category' => $category, 'subject' => $subject, 'publication_year' => $year,
                'shelf_location' => $shelf, 'status' => 'active', 'loan_policy' => $policy,
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/'.str_replace('-', '', $isbn).'-M.jpg',
            ]);

            for ($copy = 1; $copy <= $copies; $copy++) {
                BookCopy::firstOrCreate(
                    ['barcode' => sprintf('%d0492%02d', $index + 1, $copy)],
                    [
                        'library_book_id' => $book->id,
                        'copy_code' => sprintf('%s-%03d', $prefix, $copy),
                        'status' => $policy === 'reference' ? 'reference' : 'available',
                        'location' => $shelf,
                    ],
                );
            }

            $book->syncCopyCounts();

            return $book;
        })->keyBy('isbn');

        if (Loan::query()->exists()) {
            return;
        }

        $student = User::where('email', 'student@clcst.edu.ph')->first();
        $faculty = User::where('email', 'faculty@clcst.edu.ph')->first();
        $librarian = User::where('email', 'librarian@clcst.edu.ph')->first();
        if (! $student || ! $faculty || ! $librarian) {
            return;
        }

        // Current loans: one due soon, one with plenty of time left.
        $this->loan($student, $books['978-0132350884'], $librarian, -12, 2);
        $this->loan($student, $books['978-0262046305'], $librarian, -6, 8);
        $this->loan($faculty, $books['978-0078022159'], $librarian, -10, 20);
        $this->loan($faculty, $books['978-0132350884'], $librarian, -40, -3);

        // Returned history, including an on-time return, a settled late fee,
        // a waived grace-day fee, and one fee the student still owes.
        $this->loan($student, $books['978-1590282755'], $librarian, -45, -38, -40);
        $paid = $this->loan($student, $books['978-1985086593'], $librarian, -80, -73, -71);
        $waived = $this->loan($student, $books['978-1259676512'], $librarian, -120, -113, -112);
        $owed = $this->loan($student, $books['978-0073376257'], $librarian, -30, -23, -22);

        $this->fine($paid, 2, 'paid', $librarian, 'OR');
        $this->fine($waived, 1, 'waived', $librarian, 'WV');
        $this->fine($owed, 1, 'outstanding');

        Reservation::create(['user_id' => $student->id, 'library_book_id' => $books['978-0134610993']->id, 'status' => 'pending']);
        Reservation::create(['user_id' => $faculty->id, 'library_book_id' => $books['978-0130461094']->id, 'status' => 'pending']);

        LibraryRequest::create(['user_id' => $faculty->id, 'type' => 'course_reserve', 'subject' => 'HCI102 - Reserve shelf for Human-Computer Interaction', 'details' => 'Please place two copies of Dix et al. on the 3-day reserve shelf for the semester.', 'status' => 'in_review']);
        LibraryRequest::create(['user_id' => $faculty->id, 'type' => 'book_recommendation', 'subject' => 'Designing Interfaces (3rd Edition) by Tidwell, Brewer & Valencia', 'details' => 'Recommended for the HCI and UI/UX electives.', 'status' => 'submitted']);

        Announcement::create(['user_id' => $librarian->id, 'title' => 'Extended hours during final examinations', 'body' => 'The main library stays open until 9:00 PM on weekdays during the final examination period.', 'audience' => 'all', 'published_at' => now()->subDays(1)]);
        Announcement::create(['user_id' => $librarian->id, 'title' => 'New Computer Studies titles on the shelves', 'body' => 'Fresh copies of Human-Computer Interaction and Database System Concepts are now available on the 2nd floor.', 'audience' => 'student', 'published_at' => now()->subDays(4)]);
    }

    /**
     * Create a loan relative to today. Negative numbers are days in the past.
     */
    private function loan(User $patron, LibraryBook $book, User $staff, int $borrowed, int $due, ?int $returned = null): Loan
    {
        $copy = $book->copies()->where('status', 'available')->first();
        $loan = Loan::create([
            'user_id' => $patron->id,
            'book_copy_id' => $copy->id,
            'issued_by' => $staff->id,
            'received_by' => $returned !== null ? $staff->id : null,
            'receipt_no' => 'LN-'.now()->addDays($borrowed)->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(3))),
            'borrowed_at' => now()->addDays($borrowed)->setTime(9, 30),
            'due_at' => now()->addDays($due)->endOfDay(),
            'returned_at' => $returned !== null ? now()->addDays($returned)->setTime(15, 10) : null,
        ]);

        if ($returned === null) {
            $copy->update(['status' => 'on_loan']);
            $book->syncCopyCounts();
        }

        return $loan;
    }

    private function fine(Loan $loan, int $days, string $status, ?User $staff = null, ?string $prefix = null): void
    {
        $fine = Fine::create([
            'user_id' => $loan->user_id,
            'loan_id' => $loan->id,
            'reason' => 'Overdue return: '.$loan->copy->book->title,
            'days_late' => $days,
            'amount' => $days * config('library.fine_per_day'),
            'status' => $status,
            'settled_by' => $staff?->id,
            'settled_at' => $staff ? $loan->returned_at : null,
        ]);

        if ($prefix) {
            $fine->update(['receipt_no' => $prefix.'-'.$loan->returned_at->format('Ymd').'-'.str_pad((string) $fine->id, 5, '0', STR_PAD_LEFT)]);
        }
    }
}
