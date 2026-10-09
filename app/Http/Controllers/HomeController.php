<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'business_type' => ['required', 'string', 'max:255'],
        ]);

        return response('Generated successfully');
    }

    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'role' => ['required', 'in:student,faculty,staff'],
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        if ($request->input('role') === 'student') {
            return redirect()->route('student.dashboard')->with('status', 'Welcome back to your student library dashboard.');
        }

        if ($request->input('role') === 'faculty') {
            return redirect()->route('faculty.dashboard')->with('status', 'Welcome back to your faculty library dashboard.');
        }

        return redirect()->route('staff.dashboard')->with('status', 'Welcome back to the librarian operations dashboard.');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function storeRegistration(Request $request)
    {
        $request->validate([
            'patron_role' => ['required', 'in:student,faculty,staff'],
            'last_name' => ['required', 'string', 'max:100'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'patron_id' => ['required', 'string', 'max:100'],
            'program_or_department' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'mobile_number' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'credential_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'privacy_consent' => ['accepted'],
        ]);

        $request->session()->put('registered_patron', [
            'role' => $request->input('patron_role'),
            'name' => trim($request->input('first_name').' '.$request->input('last_name')),
            'id' => $request->input('patron_id'),
            'program' => $request->input('program_or_department'),
            'registered_at' => now()->format('M d, Y h:i A'),
        ]);

        $status = 'Registration submitted successfully. Your digital library pass has been issued for verification.';

        return match ($request->input('patron_role')) {
            'faculty' => redirect()->route('faculty.dashboard')->with('status', $status),
            'staff' => redirect()->route('staff.dashboard')->with('status', $status),
            default => redirect()->route('student.dashboard')->with('status', $status),
        };
    }

    public function studentDashboard()
    {
        return view('student.dashboard');
    }

    public function facultyDashboard()
    {
        return view('staff.dashboard', $this->staffDashboardData('faculty', 'dashboard'));
    }

    public function staffDashboard()
    {
        return view('staff.dashboard', $this->staffDashboardData('staff', 'dashboard'));
    }

    public function booksCatalog()
    {
        $books = collect($this->catalogBooks());
        $query = trim((string) request('q', ''));
        $field = request('field', 'all');
        $category = request('category', 'all');
        $availability = request('availability', 'all');
        $sort = request('sort', 'relevance');

        $filteredBooks = $books
            ->filter(function (array $book) use ($query, $field) {
                if ($query === '') {
                    return true;
                }

                $copyBarcodes = collect($book['copies'])->pluck('barcode')->all();

                $haystacks = match ($field) {
                    'title' => [$book['title']],
                    'author' => [$book['author']],
                    'isbn' => [$book['isbn']],
                    'call' => [$book['call']],
                    default => array_merge([$book['title'], $book['author'], $book['isbn'], $book['call'], $book['category'], $book['subject']], $copyBarcodes),
                };

                foreach ($haystacks as $value) {
                    if (str_contains(strtolower($value), strtolower($query))) {
                        return true;
                    }
                }

                return false;
            })
            ->filter(fn (array $book) => $category === 'all' || $book['category'] === $category)
            ->filter(function (array $book) use ($availability) {
                return match ($availability) {
                    'available' => $this->availableCopies($book) > 0,
                    'reference' => $book['policy'] === 'reference',
                    default => true,
                };
            });

        $filteredBooks = match ($sort) {
            'title' => $filteredBooks->sortBy('title'),
            'newest' => $filteredBooks->sortByDesc('year'),
            'available' => $filteredBooks->sortByDesc(fn (array $book) => $this->availableCopies($book)),
            default => $filteredBooks,
        };

        $books = $filteredBooks->values();
        $selectedBook = $books->firstWhere('id', request('selected')) ?? $books->first() ?? $this->catalogBooks()[0];

        return view('books.catalog', [
            'books' => $books,
            'selectedBook' => $selectedBook,
            'categories' => collect($this->catalogBooks())->pluck('category')->unique()->values(),
            'activeLoans' => $this->activeLoans(),
            'reservations' => session('reservations', []),
            'renewedLoans' => session('renewed_loans', []),
            'query' => $query,
            'field' => $field,
            'category' => $category,
            'availability' => $availability,
            'sort' => $sort,
        ]);
    }

    public function holdBook(string $book)
    {
        $catalogBook = collect($this->catalogBooks())->firstWhere('id', $book);

        abort_unless($catalogBook, 404);

        $reservations = session('reservations', []);
        $reservations[$book] = [
            'title' => $catalogBook['title'],
            'requested_at' => now()->format('M d, Y h:i A'),
        ];

        session(['reservations' => $reservations]);

        return redirect()
            ->route('books.catalog', ['selected' => $book])
            ->with('status', 'Reservation request submitted for '.$catalogBook['title'].'.');
    }

    public function cancelReservation(string $book)
    {
        $reservations = session('reservations', []);
        $reservationTitle = $reservations[$book]['title'] ?? 'selected book';

        unset($reservations[$book]);
        session(['reservations' => $reservations]);

        return redirect()
            ->back()
            ->with('status', 'Reservation cancelled for '.$reservationTitle.'.');
    }

    public function renewLoan(string $loan)
    {
        $activeLoan = collect($this->activeLoans())->firstWhere('id', $loan);

        abort_unless($activeLoan, 404);

        $renewedLoans = session('renewed_loans', []);
        $renewedLoans[$loan] = now()->addDays(7)->format('M d, Y');

        session(['renewed_loans' => $renewedLoans]);

        return redirect()
            ->back()
            ->with('status', 'Renewal request submitted for '.$activeLoan['title'].'. New requested due date: '.$renewedLoans[$loan].'.');
    }

    public function payFine(string $fine)
    {
        $ledgerItem = collect($this->fineLedger())->firstWhere('id', $fine);

        abort_unless($ledgerItem, 404);

        $paidFines = session('paid_fines', []);
        $paidFines[$fine] = now()->format('M d, Y h:i A');

        session(['paid_fines' => $paidFines]);

        return redirect()
            ->back()
            ->with('status', 'Payment recorded for '.$ledgerItem['title'].'. Official receipt queued for printing.');
    }

    public function studentSection(string $section)
    {
        $titles = [
            'borrow-return' => 'Borrow / Return',
            'reservations' => 'Reservations',
            'history' => 'Borrowing History',
            'fines' => 'Fines & Penalties',
            'notifications' => 'Notifications',
            'account' => 'My Account',
            'support' => 'Help & Support',
            'scan' => 'Shelf Barcode Scanner',
            'circulation-policy' => 'Circulation Policy',
            'opac-guide' => 'OPAC Guide',
        ];

        if ($section === 'borrow-return') {
            return view('student.borrow-return', [
                'title' => $titles[$section],
                'section' => $section,
                'reservations' => session('reservations', []),
                'renewedLoans' => session('renewed_loans', []),
                'activeLoans' => $this->activeLoans(),
                'catalogBooks' => $this->catalogBooks(),
            ]);
        }

        if ($section === 'reservations') {
            return view('student.reservations', [
                'title' => $titles[$section],
                'section' => $section,
                'reservations' => session('reservations', []),
                'renewedLoans' => session('renewed_loans', []),
                'activeLoans' => $this->activeLoans(),
                'catalogBooks' => $this->catalogBooks(),
            ]);
        }

        if ($section === 'history') {
            return view('student.history', [
                'title' => $titles[$section],
                'section' => $section,
                'reservations' => session('reservations', []),
                'renewedLoans' => session('renewed_loans', []),
                'activeLoans' => $this->activeLoans(),
                'historyRows' => $this->borrowingHistory(),
            ]);
        }

        if ($section === 'fines') {
            return view('student.fines', [
                'title' => $titles[$section],
                'section' => $section,
                'reservations' => session('reservations', []),
                'renewedLoans' => session('renewed_loans', []),
                'activeLoans' => $this->activeLoans(),
                'fineRows' => $this->fineLedger(),
                'paidFines' => session('paid_fines', []),
            ]);
        }

        if ($section === 'account') {
            return view('student.account', [
                'title' => $titles[$section],
                'section' => $section,
                'reservations' => session('reservations', []),
                'renewedLoans' => session('renewed_loans', []),
                'activeLoans' => $this->activeLoans(),
                'fineRows' => $this->fineLedger(),
                'paidFines' => session('paid_fines', []),
            ]);
        }

        if ($section === 'notifications') {
            return view('student.notifications', [
                'title' => $titles[$section],
                'section' => $section,
                'reservations' => session('reservations', []),
                'renewedLoans' => session('renewed_loans', []),
                'activeLoans' => $this->activeLoans(),
            ]);
        }

        if ($section === 'support') {
            return view('student.support', [
                'title' => $titles[$section],
                'section' => $section,
                'reservations' => session('reservations', []),
                'renewedLoans' => session('renewed_loans', []),
                'activeLoans' => $this->activeLoans(),
                'fineRows' => $this->fineLedger(),
                'paidFines' => session('paid_fines', []),
            ]);
        }

        return view('student.section', [
            'title' => $titles[$section],
            'section' => $section,
            'reservations' => session('reservations', []),
            'renewedLoans' => session('renewed_loans', []),
            'activeLoans' => $this->activeLoans(),
        ]);
    }

    public function staffSection(string $section)
    {
        $titles = [
            'circulation' => 'My Borrowed Books',
            'patrons' => 'Patron Records',
            'profile' => 'My Profile',
            'catalog' => 'Recommend a Book',
            'reservations' => 'My Reservations',
            'reports' => 'Renewal Requests',
            'history' => 'Borrowing History',
            'academic' => 'Faculty Academic Requests',
            'course-reserves' => 'Course Syllabus Reserves',
            'reading-lists' => 'Course Reading Lists',
            'instruction-sessions' => 'Library Instruction Sessions',
            'notifications' => 'Faculty Notifications',
            'settings' => 'System Settings',
            'support' => 'Help & Support',
        ];

        return view('staff.section', array_merge(
            $this->staffDashboardData('staff', $section),
            [
                'title' => $titles[$section],
                'section' => $section,
            ],
        ));
    }

    private function staffDashboardData(string $portalRole, string $activePage): array
    {
        $books = $this->catalogBooks();
        $activeLoans = $this->activeLoans();
        $reservations = session('reservations', []);
        $fineRows = $this->fineLedger();

        return [
            'portalRole' => $portalRole,
            'activePage' => $activePage,
            'catalogBooks' => $books,
            'activeLoans' => $activeLoans,
            'borrowingHistory' => $this->borrowingHistory(),
            'renewedLoans' => session('renewed_loans', []),
            'reservations' => $reservations,
            'fineRows' => $fineRows,
            'stats' => [
                'activeLoans' => count($activeLoans),
                'availableCopies' => collect($books)->sum(fn (array $book) => $this->availableCopies($book)),
                'pendingReservations' => max(count($reservations), 2),
                'outstandingFines' => collect($fineRows)->where('status', 'outstanding')->sum('amount'),
            ],
            'circulationQueue' => $this->circulationQueue(),
            'facultyRequests' => $this->facultyRequests(),
            'patronRecords' => $this->patronRecords(),
        ];
    }

    private function circulationQueue(): array
    {
        return [
            ['ticket' => 'CIR-1082', 'type' => 'Return Check-In', 'patron' => 'Juan Dela Cruz', 'item' => 'Clean Code', 'status' => 'Due in 2 days', 'priority' => 'High'],
            ['ticket' => 'CIR-1083', 'type' => 'Hold Pickup', 'patron' => 'Seanne Ynna Alonzo', 'item' => 'Computer Networks', 'status' => 'Shelf scan needed', 'priority' => 'Medium'],
            ['ticket' => 'CIR-1084', 'type' => 'Renewal Review', 'patron' => 'Miguel Santos', 'item' => 'Introduction to Algorithms', 'status' => 'Eligible', 'priority' => 'Normal'],
        ];
    }

    private function facultyRequests(): array
    {
        return [
            ['course' => 'CS 211 - Object-Oriented Programming', 'request' => 'Reserve shelf for Python and software engineering titles', 'owner' => 'Prof. Elena Reyes', 'status' => 'Ready for review'],
            ['course' => 'IT 312 - Network Administration', 'request' => 'Add Computer Networks to room-use reference list', 'owner' => 'Engr. Mark Ramos', 'status' => 'Pending copy check'],
            ['course' => 'CAP 401 - Research Methods', 'request' => 'Prepare bibliography support for thesis groups', 'owner' => 'Dr. Liza Mercado', 'status' => 'Scheduled'],
        ];
    }

    private function patronRecords(): array
    {
        return [
            ['name' => 'Juan Dela Cruz', 'id' => '2024-10492-OL', 'program' => 'BS Computer Science III', 'loans' => 2, 'status' => 'Active'],
            ['name' => 'Seanne Ynna Alonzo', 'id' => '2024-10491-OL', 'program' => 'BS Information Technology II', 'loans' => 1, 'status' => 'Hold pending'],
            ['name' => 'Miguel Santos', 'id' => '2023-11822-OL', 'program' => 'BS Computer Engineering IV', 'loans' => 3, 'status' => 'Clearance check'],
        ];
    }

    private function catalogBooks(): array
    {
        return [
            [
                'id' => 'python-programming',
                'category' => 'Computer Studies & IT',
                'subject' => 'Programming',
                'publisher' => 'CLCST Main Stacks',
                'title' => 'Introduction to Python Programming',
                'author' => 'John Zelle',
                'year' => '2021',
                'call' => 'QA76.73.P98 Z45 2021',
                'isbn' => '978-1590282755',
                'location' => 'Stacks 2B, Shelf 04',
                'loan' => 'Standard 7-Day Student Loan',
                'policy' => 'circulating',
                'cover' => 'from-emerald-700 to-slate-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9781590282755-M.jpg',
                'icon' => 'code',
                'badge' => 'Python',
                'copies' => [
                    ['copy' => 'PY-001', 'status' => 'available', 'location' => '2nd Flr Stacks 2B', 'barcode' => '1049281'],
                    ['copy' => 'PY-002', 'status' => 'available', 'location' => '2nd Flr Stacks 2B', 'barcode' => '1049282'],
                    ['copy' => 'PY-003', 'status' => 'on_loan', 'location' => 'Due Oct 31, 2024', 'barcode' => '1049283'],
                    ['copy' => 'PY-004', 'status' => 'available', 'location' => 'Reserve Desk', 'barcode' => '1049284'],
                    ['copy' => 'PY-005', 'status' => 'available', 'location' => '2nd Flr Stacks 2B', 'barcode' => '1049285'],
                ],
            ],
            [
                'id' => 'clean-code',
                'category' => 'Computer Studies & IT',
                'subject' => 'Software Engineering',
                'publisher' => 'Addison-Wesley',
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'year' => '2008',
                'call' => 'QA76.76.C64 M37',
                'isbn' => '978-0132350884',
                'location' => 'Stacks 2B, Shelf 06',
                'loan' => 'Currently high demand',
                'policy' => 'circulating',
                'cover' => 'from-yellow-700 to-slate-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780132350884-M.jpg',
                'icon' => 'terminal',
                'badge' => 'Due Soon',
                'copies' => [
                    ['copy' => 'CC-001', 'status' => 'on_loan', 'location' => 'Due Oct 26, 2024', 'barcode' => '2049101'],
                    ['copy' => 'CC-002', 'status' => 'on_loan', 'location' => 'Due Oct 28, 2024', 'barcode' => '2049102'],
                    ['copy' => 'CC-003', 'status' => 'on_loan', 'location' => 'Due Nov 02, 2024', 'barcode' => '2049103'],
                    ['copy' => 'CC-004', 'status' => 'on_loan', 'location' => 'Due Nov 04, 2024', 'barcode' => '2049104'],
                ],
            ],
            [
                'id' => 'algorithms',
                'category' => 'Computer Studies & IT',
                'subject' => 'Algorithms',
                'publisher' => 'MIT Press',
                'title' => 'Introduction to Algorithms (4th Edition)',
                'author' => 'Cormen, Leiserson, Rivest, Stein',
                'year' => '2022',
                'call' => 'QA76.6 .I585',
                'isbn' => '978-0262046305',
                'location' => 'Stacks 2A, Shelf 01',
                'loan' => 'Standard 7-Day Student Loan',
                'policy' => 'circulating',
                'cover' => 'from-blue-800 to-indigo-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780262046305-M.jpg',
                'icon' => 'functions',
                'badge' => 'CLRS',
                'copies' => [
                    ['copy' => 'AL-001', 'status' => 'available', 'location' => 'Stacks 2A', 'barcode' => '3049201'],
                    ['copy' => 'AL-002', 'status' => 'on_loan', 'location' => 'Due Nov 01, 2024', 'barcode' => '3049202'],
                    ['copy' => 'AL-003', 'status' => 'on_loan', 'location' => 'Due Nov 06, 2024', 'barcode' => '3049203'],
                ],
            ],
            [
                'id' => 'ai-modern-approach',
                'category' => 'Computer Studies & IT',
                'subject' => 'Artificial Intelligence',
                'publisher' => 'Pearson Higher Ed',
                'title' => 'Artificial Intelligence: A Modern Approach (4th Global Edition)',
                'author' => 'Stuart Russell & Peter Norvig',
                'year' => '2020',
                'call' => 'Q335 .R86 2020',
                'isbn' => '978-0134610993',
                'location' => 'Stacks 3A, Shelf 05',
                'loan' => 'Standard 7-Day Student Loan',
                'policy' => 'circulating',
                'cover' => 'from-emerald-800 to-teal-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780134610993-M.jpg',
                'icon' => 'smart_toy',
                'badge' => '4th Ed',
                'copies' => [
                    ['copy' => 'AI-001', 'status' => 'available', 'location' => 'Stacks 3A', 'barcode' => '4049301'],
                    ['copy' => 'AI-002', 'status' => 'available', 'location' => 'Stacks 3A', 'barcode' => '4049302'],
                ],
            ],
            [
                'id' => 'computer-networks',
                'category' => 'Engineering',
                'subject' => 'Networking',
                'publisher' => 'Morgan Kaufmann / Elsevier',
                'title' => 'Computer Networks: A Systems Approach (6th Edition)',
                'author' => 'Larry L. Peterson & Bruce S. Davie',
                'year' => '2021',
                'call' => 'TK5105.5 .P479 2021',
                'isbn' => '978-0128182000',
                'location' => 'Room 102 Reference Section Desk',
                'loan' => 'In-Library Use Only',
                'policy' => 'reference',
                'cover' => 'from-amber-700 to-amber-900',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780128182000-M.jpg',
                'icon' => 'hub',
                'badge' => 'Ref Only',
                'copies' => [
                    ['copy' => 'CN-001', 'status' => 'reference', 'location' => 'Room 102', 'barcode' => '5049401'],
                    ['copy' => 'CN-002', 'status' => 'reference', 'location' => 'Room 102', 'barcode' => '5049402'],
                ],
            ],
            [
                'id' => 'object-oriented-se',
                'category' => 'Computer Studies & IT',
                'subject' => 'Software Engineering',
                'publisher' => 'McGraw-Hill',
                'title' => 'Object-Oriented Software Engineering: An Agile Unified Methodology',
                'author' => 'David Kung',
                'year' => '2014',
                'call' => 'QA76.758 .K86',
                'isbn' => 'Available in catalog record',
                'location' => 'Stacks 2B, Shelf 06',
                'loan' => 'Standard 7-Day Student Loan',
                'policy' => 'circulating',
                'cover' => 'from-cyan-900 to-blue-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780073376257-M.jpg',
                'icon' => 'account_tree',
                'badge' => 'Agile',
                'copies' => [
                    ['copy' => 'SE-001', 'status' => 'available', 'location' => 'Stacks 2B', 'barcode' => '6049501'],
                    ['copy' => 'SE-002', 'status' => 'available', 'location' => 'Stacks 2B', 'barcode' => '6049502'],
                    ['copy' => 'SE-003', 'status' => 'available', 'location' => 'Stacks 2B', 'barcode' => '6049503'],
                ],
            ],
        ];
    }

    private function activeLoans(): array
    {
        return [
            ['id' => 'loan-clean-code', 'book_id' => 'clean-code', 'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship', 'due' => 'Oct 26, 2024', 'barcode' => 'CC-001', 'call' => 'QA76.76.C64 M37'],
            ['id' => 'loan-algorithms', 'book_id' => 'algorithms', 'title' => 'Introduction to Algorithms (4th Edition)', 'due' => 'Nov 01, 2024', 'barcode' => 'AL-002', 'call' => 'QA76.6 .I585'],
        ];
    }

    private function borrowingHistory(): array
    {
        return [
            ['loan_id' => 'loan-clean-code', 'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship', 'author' => 'Robert C. Martin', 'call' => 'QA76.76.C64 M37', 'classification' => 'CS (004)', 'barcode' => 'CC-001', 'borrowed' => 'Oct 12, 2024', 'desk' => 'Desk 01 - Ms. Santos', 'due' => 'Oct 26, 2024', 'returned' => 'Currently on loan', 'note' => '2 days left', 'status' => 'active', 'receipt' => 'LS-2024-10492-CC'],
            ['loan_id' => 'loan-algorithms', 'title' => 'Introduction to Algorithms (4th Edition)', 'author' => 'Cormen, Leiserson, Rivest, Stein', 'call' => 'QA76.6 .I585', 'classification' => 'CS (004)', 'barcode' => 'AL-002', 'borrowed' => 'Oct 15, 2024', 'desk' => 'Desk 02 - Mr. Ramos', 'due' => 'Nov 01, 2024', 'returned' => 'Currently on loan', 'note' => 'renewable', 'status' => 'active', 'receipt' => 'LS-2024-10492-AL'],
            ['loan_id' => null, 'title' => 'Introduction to Python Programming', 'author' => 'John Zelle', 'call' => 'QA76.73.P98 Z45 2021', 'classification' => 'CS (004)', 'barcode' => 'PY-001', 'borrowed' => 'Sep 10, 2024', 'desk' => 'Desk 01 - Ms. Santos', 'due' => 'Sep 24, 2024', 'returned' => 'Sep 22, 2024', 'note' => 'Returned 2 days early', 'status' => 'returned', 'receipt' => 'RC-2024-7712'],
            ['loan_id' => null, 'title' => 'Database System Concepts (7th Edition)', 'author' => 'Silberschatz, Korth, Sudarshan', 'call' => 'QA76.9.D3 S56', 'classification' => 'CS (004)', 'barcode' => 'DS-102', 'borrowed' => 'Aug 20, 2024', 'desk' => 'Desk 01 - Ms. Santos', 'due' => 'Sep 03, 2024', 'returned' => 'Sep 03, 2024', 'note' => 'Returned on due date', 'status' => 'returned', 'receipt' => 'RC-2024-6988'],
            ['loan_id' => null, 'title' => 'Operating Systems: Three Easy Pieces', 'author' => 'Arpaci-Dusseau', 'call' => 'QA76.76.O63', 'classification' => 'CS (004)', 'barcode' => 'OS-119', 'borrowed' => 'Aug 01, 2024', 'desk' => 'Desk 02 - Mr. Ramos', 'due' => 'Aug 15, 2024', 'returned' => 'Aug 16, 2024', 'note' => '1 day grace policy applied', 'status' => 'grace', 'receipt' => 'RC-2024-6405'],
            ['loan_id' => null, 'title' => 'Discrete Mathematics and Its Applications', 'author' => 'Kenneth H. Rosen', 'call' => 'QA39.3 .R67', 'classification' => 'MATH (510)', 'barcode' => 'DM-082', 'borrowed' => 'May 12, 2024', 'desk' => 'Term 2 AY 2023-2024', 'due' => 'May 26, 2024', 'returned' => 'May 25, 2024', 'note' => 'Returned 1 day early', 'status' => 'returned', 'receipt' => 'RC-2024-5220'],
            ['loan_id' => null, 'title' => 'Computer Networks', 'author' => 'Andrew S. Tanenbaum', 'call' => 'TK5105.5 .T36', 'classification' => 'CS (004)', 'barcode' => 'CN-015', 'borrowed' => 'Apr 02, 2024', 'desk' => 'Desk 01 - Ms. Santos', 'due' => 'Apr 16, 2024', 'returned' => 'Apr 18, 2024', 'note' => 'Fine PHP 15.00 settled', 'status' => 'settled', 'receipt' => 'RC-2024-8812'],
            ['loan_id' => null, 'title' => 'Software Engineering: A Practitioner\'s Approach', 'author' => 'Roger S. Pressman', 'call' => 'QA76.758 .P75', 'classification' => 'CS (004)', 'barcode' => 'SE-304', 'borrowed' => 'Feb 14, 2024', 'desk' => 'Desk 02 - Mr. Ramos', 'due' => 'Feb 28, 2024', 'returned' => 'Feb 27, 2024', 'note' => 'Returned 1 day early', 'status' => 'returned', 'receipt' => 'RC-2024-4410'],
        ];
    }

    private function fineLedger(): array
    {
        return [
            ['id' => 'fine-cn-015', 'title' => 'Computer Networks', 'copy' => 'CN-015', 'call' => 'TK5105.5 .T36', 'borrowed' => 'Apr 02, 2024', 'due' => 'Apr 16, 2024', 'returned' => 'Apr 18, 2024', 'days' => 2, 'rate' => 5, 'amount' => 10, 'status' => 'settled', 'receipt' => 'OR-2024-8812', 'paid_at' => 'Apr 18, 2024'],
            ['id' => 'fine-os-119', 'title' => 'Operating Systems: Three Easy Pieces', 'copy' => 'OS-119', 'call' => 'QA76.76.O63', 'borrowed' => 'Aug 01, 2024', 'due' => 'Aug 15, 2024', 'returned' => 'Aug 16, 2024', 'days' => 1, 'rate' => 5, 'amount' => 5, 'status' => 'waived', 'receipt' => 'WV-2024-6405', 'paid_at' => 'Aug 16, 2024'],
            ['id' => 'fine-clean-code', 'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship', 'copy' => 'CC-001', 'call' => 'QA76.76.C64 M37', 'borrowed' => 'Oct 12, 2024', 'due' => 'Oct 26, 2024', 'returned' => 'Currently on loan', 'days' => 0, 'rate' => 5, 'amount' => 0, 'status' => 'watchlist', 'receipt' => 'Pending', 'paid_at' => null],
            ['id' => 'fine-lab-manual', 'title' => 'Programming Laboratory Manual', 'copy' => 'PL-044', 'call' => 'QA76.73 .L33', 'borrowed' => 'Jul 10, 2024', 'due' => 'Jul 24, 2024', 'returned' => 'Jul 25, 2024', 'days' => 1, 'rate' => 5, 'amount' => 5, 'status' => 'outstanding', 'receipt' => 'Pending', 'paid_at' => null],
        ];
    }

    private function availableCopies(array $book): int
    {
        return collect($book['copies'])->where('status', 'available')->count();
    }
}
