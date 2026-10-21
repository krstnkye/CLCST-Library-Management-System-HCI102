<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LibraryBook;
use App\Models\User;
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
        return redirect()->route('librarian.dashboard');
    }

    public function librarianDashboard()
    {
        return view('librarian.dashboard', $this->librarianDashboardData('dashboard'));
    }

    public function adminDashboard()
    {
        return view('administrator.dashboard', $this->administratorData('dashboard'));
    }

    public function adminSection(string $section)
    {
        $pages = $this->administratorPages();
        abort_unless(isset($pages[$section]), 404);

        if ($section === 'bulk-data') {
            return redirect()->route('management.data.index');
        }

        return view('administrator.section', array_merge(
            $this->administratorData($section),
            ['section' => $section, 'page' => $pages[$section]],
        ));
    }

    public function superAdminDashboard()
    {
        return view('admin.dashboard', $this->superAdminData('dashboard'));
    }

    public function superAdminSection(string $section)
    {
        if (in_array($section, ['users', 'approvals'], true)) {
            return redirect()->route('management.accounts.index');
        }

        if ($section === 'data-export') {
            return redirect()->route('management.data.index');
        }

        $pages = $this->superAdminPages();

        abort_unless(isset($pages[$section]), 404);

        return view('admin.section', array_merge(
            $this->superAdminData($section),
            [
                'section' => $section,
                'page' => $pages[$section],
            ],
        ));
    }

    public function librarianSection(string $section)
    {
        if ($section === 'verification') {
            return redirect()->route('management.accounts.index');
        }

        $pages = $this->librarianPages();

        abort_unless(isset($pages[$section]), 404);

        return view('librarian.section', array_merge(
            $this->librarianDashboardData($section),
            [
                'section' => $section,
                'page' => $pages[$section],
            ],
        ));
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
            'circulation-policy' => 'Borrowing Policy',
            'opac-guide' => 'Book Catalog Guide',
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
            'patrons' => 'Library User Records',
            'profile' => 'My Profile',
            'catalog' => 'Recommend a Book',
            'reservations' => 'My Reservations',
            'reports' => 'Renewal Requests',
            'history' => 'Borrowing History',
            'academic' => 'Academic Requests',
            'course-reserves' => 'Course Reserves',
            'reading-lists' => 'Reading Lists',
            'instruction-sessions' => 'Library Class Sessions',
            'inter-library-loan' => 'Inter-Library Loan',
            'notifications' => 'Faculty Notifications',
            'feedback' => 'Library Feedback',
            'settings' => 'System Settings',
            'support' => 'Help & Support',
        ];

        return view('staff.section', array_merge(
            $this->staffDashboardData('faculty', $section),
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

    private function librarianDashboardData(string $activePage): array
    {
        $books = $this->catalogBooks();
        $activeLoans = $this->activeLoans();
        $patrons = $this->patronRecords();
        $storedBooks = LibraryBook::query();
        $hasStoredBooks = $storedBooks->exists();
        $availableCopies = $hasStoredBooks
            ? (clone $storedBooks)->sum('available_copies')
            : collect($books)->sum(fn (array $book) => $this->availableCopies($book));

        return [
            'activePage' => $activePage,
            'catalogBooks' => $books,
            'activeLoans' => $activeLoans,
            'patronRecords' => $patrons,
            'circulationQueue' => $this->circulationQueue(),
            'stats' => [
                'titles' => $hasStoredBooks ? (clone $storedBooks)->count() : count($books),
                'availableCopies' => $availableCopies,
                'borrowedBooks' => count($activeLoans),
                'overdueBooks' => 1,
                'registeredUsers' => User::count() ?: count($patrons),
                'pendingRequests' => User::where('status', 'pending')->count(),
            ],
            'activities' => [
                ['time' => '9:42 AM', 'action' => 'Book returned', 'detail' => 'Introduction to Python Programming - Copy PY-002', 'icon' => 'assignment_return'],
                ['time' => '9:18 AM', 'action' => 'Loan approved', 'detail' => 'Computer Networks for Juan Dela Cruz', 'icon' => 'check_circle'],
                ['time' => '8:55 AM', 'action' => 'New account submitted', 'detail' => 'Student registration awaiting document review', 'icon' => 'person_add'],
                ['time' => '8:30 AM', 'action' => 'Inventory updated', 'detail' => 'Four copies verified in Computer Studies shelves', 'icon' => 'inventory_2'],
            ],
        ];
    }

    private function superAdminData(string $activePage): array
    {
        $pending = User::where('status', 'pending')->count();
        $activities = AuditLog::query()->latest()->limit(8)->get()->map(function (AuditLog $log): array {
            return [
                'time' => $log->created_at->format('M d, g:i A'),
                'user' => $log->user_id ? (User::find($log->user_id)?->name ?? 'Former user') : 'System',
                'action' => $log->description,
                'area' => str_starts_with($log->event, 'login') ? 'Security' : 'System',
                'icon' => str_starts_with($log->event, 'login') ? 'login' : 'history',
            ];
        })->all();

        return [
            'activePage' => $activePage,
            'systemStats' => [
                'users' => User::count(),
                'staff' => User::whereIn('role', ['librarian', 'admin', 'superadmin'])->count(),
                'patrons' => User::whereIn('role', ['student', 'faculty'])->count(),
                'bookTitles' => LibraryBook::count(),
                'transactions' => AuditLog::count(),
                'pendingApprovals' => $pending,
            ],
            'systemActivities' => $activities,
            'systemAlerts' => [
                ['level' => 'Review', 'message' => $pending.' account request(s) require verification.', 'icon' => 'person_alert', 'tone' => 'amber'],
                ['level' => 'Security', 'message' => 'Role checks, account status checks, and sign-in throttling are active.', 'icon' => 'verified_user', 'tone' => 'blue'],
                ['level' => 'Data', 'message' => LibraryBook::count().' database-backed book record(s) are available for managed import and export.', 'icon' => 'database', 'tone' => 'green'],
            ],
        ];
    }

    private function administratorData(string $activePage): array
    {
        return [
            'activePage' => $activePage,
            'stats' => [
                'users' => User::count(),
                'pending' => User::where('status', 'pending')->count(),
                'books' => LibraryBook::count(),
                'activeBooks' => LibraryBook::where('status', 'active')->count(),
            ],
            'recentUsers' => User::query()->latest()->limit(6)->get(),
            'recentActivity' => AuditLog::query()->latest()->limit(8)->get(),
            'users' => User::query()->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'active' THEN 1 ELSE 2 END")->orderBy('name')->limit(100)->get(),
            'storedBooks' => LibraryBook::query()->orderBy('title')->limit(100)->get(),
        ];
    }

    private function administratorPages(): array
    {
        return [
            'users' => ['title' => 'User Accounts', 'description' => 'Find, review, and update library user accounts.', 'icon' => 'group'],
            'approvals' => ['title' => 'Account Approvals', 'description' => 'Review identity documents before activating accounts.', 'icon' => 'how_to_reg'],
            'books' => ['title' => 'Book Records', 'description' => 'Maintain catalog records and copy counts.', 'icon' => 'menu_book'],
            'circulation' => ['title' => 'Library Transactions', 'description' => 'Review borrowing, returns, renewals, and reservations.', 'icon' => 'sync_alt'],
            'reports' => ['title' => 'Reports', 'description' => 'View clear summaries of library use and collection activity.', 'icon' => 'bar_chart'],
            'bulk-data' => ['title' => 'Import and Export', 'description' => 'Move user and book records using checked CSV files.', 'icon' => 'upload_file'],
            'audit-logs' => ['title' => 'Activity Log', 'description' => 'Review important security and data-management actions.', 'icon' => 'history'],
            'announcements' => ['title' => 'Announcements', 'description' => 'Publish clear notices for library users.', 'icon' => 'campaign'],
            'profile' => ['title' => 'My Profile', 'description' => 'Review your administrator account details.', 'icon' => 'account_circle'],
        ];
    }

    private function superAdminPages(): array
    {
        return [
            'users' => ['title' => 'All Users', 'description' => 'Manage every registered student, faculty, staff, librarian, and administrator account.', 'icon' => 'group', 'action' => 'Add User'],
            'roles' => ['title' => 'Roles and Permissions', 'description' => 'Control what each account type can view and manage.', 'icon' => 'admin_panel_settings', 'action' => 'Create Role'],
            'approvals' => ['title' => 'Account Approvals', 'description' => 'Review pending registrations before granting system access.', 'icon' => 'approval', 'action' => 'Review Next'],
            'librarians' => ['title' => 'Librarians', 'description' => 'Manage librarian accounts, assignments, and account status.', 'icon' => 'local_library', 'action' => 'Add Librarian'],
            'library-staff' => ['title' => 'Library Staff', 'description' => 'Manage library assistants, desk staff, and assigned responsibilities.', 'icon' => 'badge', 'action' => 'Add Staff'],
            'branches' => ['title' => 'Library Branches', 'description' => 'Manage campus library locations, branch details, and assigned staff.', 'icon' => 'account_balance', 'action' => 'Add Branch'],
            'borrowing-rules' => ['title' => 'Borrowing Rules', 'description' => 'Set book limits, borrowing periods, and renewal limits for each role.', 'icon' => 'rule', 'action' => 'Save Rules'],
            'fine-settings' => ['title' => 'Fine Settings', 'description' => 'Set overdue charges, maximum penalties, and payment rules.', 'icon' => 'payments', 'action' => 'Save Settings'],
            'system-policies' => ['title' => 'System Policies', 'description' => 'Manage registration requirements, password rules, and approval settings.', 'icon' => 'policy', 'action' => 'Update Policies'],
            'reports' => ['title' => 'Reports and Analytics', 'description' => 'Review system-wide user, library, staff, and usage reports.', 'icon' => 'monitoring', 'action' => 'Create Report'],
            'database-backup' => ['title' => 'Database Backup', 'description' => 'Create backups and review the backup schedule and history.', 'icon' => 'backup', 'action' => 'Create Backup'],
            'database-restore' => ['title' => 'Restore Database', 'description' => 'Restore system data from a verified previous backup.', 'icon' => 'settings_backup_restore', 'action' => 'Choose Backup'],
            'data-export' => ['title' => 'Data Export', 'description' => 'Export books, users, transactions, and reports in standard formats.', 'icon' => 'download', 'action' => 'Export Data'],
            'security' => ['title' => 'Security Management', 'description' => 'Manage password policies, sessions, access monitoring, and sign-in security.', 'icon' => 'security', 'action' => 'Review Security'],
            'audit-logs' => ['title' => 'Audit Logs', 'description' => 'Track sign-ins, account changes, deleted records, and permission updates.', 'icon' => 'history', 'action' => 'Export Logs'],
            'announcements' => ['title' => 'Announcements', 'description' => 'Create and schedule system-wide notices and policy updates.', 'icon' => 'campaign', 'action' => 'New Announcement'],
            'content' => ['title' => 'Content Management', 'description' => 'Manage library information, frequently asked questions, policies, and terms.', 'icon' => 'article', 'action' => 'Add Content'],
            'notifications' => ['title' => 'Notification Management', 'description' => 'Configure overdue, approval, announcement, and maintenance alerts.', 'icon' => 'notifications', 'action' => 'Save Notifications'],
            'maintenance' => ['title' => 'System Maintenance', 'description' => 'Monitor system status, temporary data, and maintenance settings.', 'icon' => 'build', 'action' => 'Run System Check'],
            'profile' => ['title' => 'Super Admin Profile', 'description' => 'Update your profile, password, and personal security settings.', 'icon' => 'account_circle', 'action' => 'Edit Profile'],
        ];
    }

    private function librarianPages(): array
    {
        return [
            'books' => ['title' => 'Book Catalog', 'description' => 'Search, review, and update all library books.', 'icon' => 'menu_book', 'action' => 'Add Book'],
            'add-book' => ['title' => 'Add New Book', 'description' => 'Create a catalog record and enter copy information.', 'icon' => 'library_add', 'action' => 'Save Book'],
            'categories' => ['title' => 'Book Categories', 'description' => 'Manage the categories used to organize library materials.', 'icon' => 'category', 'action' => 'Add Category'],
            'inventory' => ['title' => 'Book Inventory', 'description' => 'Track available, borrowed, damaged, lost, and missing copies.', 'icon' => 'inventory_2', 'action' => 'Update Inventory'],
            'borrow-requests' => ['title' => 'Borrow Requests', 'description' => 'Review requests and issue approved books to borrowers.', 'icon' => 'outbox', 'action' => 'Issue Book'],
            'returns' => ['title' => 'Return Books', 'description' => 'Check in returned books and record their condition.', 'icon' => 'assignment_return', 'action' => 'Record Return'],
            'overdue' => ['title' => 'Overdue Books', 'description' => 'Review late books, reminders, and payment status.', 'icon' => 'schedule', 'action' => 'Send Reminder'],
            'users' => ['title' => 'Library Users', 'description' => 'Manage student, faculty, staff, and researcher accounts.', 'icon' => 'group', 'action' => 'Add User'],
            'verification' => ['title' => 'Account Verification', 'description' => 'Review documents and approve new library accounts.', 'icon' => 'how_to_reg', 'action' => 'Review Next'],
            'memberships' => ['title' => 'Memberships', 'description' => 'Track active, expired, and renewal requests.', 'icon' => 'card_membership', 'action' => 'Renew Membership'],
            'reservations' => ['title' => 'Reservations', 'description' => 'Manage pending requests, pickup notices, and waiting lists.', 'icon' => 'bookmark', 'action' => 'Review Queue'],
            'digital-resources' => ['title' => 'Digital Resources', 'description' => 'Manage e-books, journals, papers, and online references.', 'icon' => 'cloud_upload', 'action' => 'Add Resource'],
            'fines' => ['title' => 'Fines and Payments', 'description' => 'Record charges, payments, and official receipts.', 'icon' => 'payments', 'action' => 'Record Payment'],
            'reports' => ['title' => 'Reports and Analytics', 'description' => 'Review book, user, transaction, and payment reports.', 'icon' => 'monitoring', 'action' => 'Create Report'],
            'announcements' => ['title' => 'Announcements', 'description' => 'Publish library schedules, notices, events, and new arrivals.', 'icon' => 'campaign', 'action' => 'New Announcement'],
            'feedback' => ['title' => 'Feedback and Requests', 'description' => 'Respond to questions, suggestions, complaints, and book requests.', 'icon' => 'forum', 'action' => 'Open Inbox'],
            'staff-management' => ['title' => 'Library Staff', 'description' => 'Manage librarian roles, permissions, and staff accounts.', 'icon' => 'badge', 'action' => 'Add Staff'],
            'activity-logs' => ['title' => 'Activity Logs', 'description' => 'Review important system actions and account activity.', 'icon' => 'history', 'action' => 'Export Log'],
            'settings' => ['title' => 'Library Settings', 'description' => 'Set borrowing limits, due dates, fine rates, and permissions.', 'icon' => 'settings', 'action' => 'Save Settings'],
            'notifications' => ['title' => 'Notifications', 'description' => 'Review alerts that need librarian attention.', 'icon' => 'notifications', 'action' => 'Mark All Read'],
            'profile' => ['title' => 'My Profile', 'description' => 'Update your account details, password, and notification choices.', 'icon' => 'account_circle', 'action' => 'Edit Profile'],
            'support' => ['title' => 'Help and Support', 'description' => 'Find answers and contact technical support.', 'icon' => 'help', 'action' => 'Contact Support'],
        ];
    }

    private function circulationQueue(): array
    {
        return [
            ['ticket' => 'CIR-1082', 'type' => 'Return Check-In', 'patron' => 'Juan Dela Cruz', 'item' => 'Clean Code', 'status' => 'Due in 2 days', 'priority' => 'High'],
            ['ticket' => 'CIR-1083', 'type' => 'Hold Pickup', 'patron' => 'Seanne Ynna Alonzo', 'item' => 'Computer Networks', 'status' => 'Shelf scan needed', 'priority' => 'Medium'],
            ['ticket' => 'CIR-1084', 'type' => 'Renewal Review', 'patron' => 'Miguel Santos', 'item' => 'Introduction to Algorithms', 'status' => 'Allowed', 'priority' => 'Normal'],
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
                'publisher' => 'CLCST Main Shelves',
                'title' => 'Introduction to Python Programming',
                'author' => 'John Zelle',
                'year' => '2021',
                'call' => 'QA76.73.P98 Z45 2021',
                'isbn' => '978-1590282755',
                'location' => 'Shelves 2B, Shelf 04',
                'loan' => 'Standard 7-Day Student Loan',
                'policy' => 'circulating',
                'cover' => 'from-emerald-700 to-slate-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9781590282755-M.jpg',
                'icon' => 'code',
                'badge' => 'Python',
                'copies' => [
                    ['copy' => 'PY-001', 'status' => 'available', 'location' => '2nd Flr Shelves 2B', 'barcode' => '1049281'],
                    ['copy' => 'PY-002', 'status' => 'available', 'location' => '2nd Flr Shelves 2B', 'barcode' => '1049282'],
                    ['copy' => 'PY-003', 'status' => 'on_loan', 'location' => 'Due Oct 31, 2024', 'barcode' => '1049283'],
                    ['copy' => 'PY-004', 'status' => 'available', 'location' => 'Library Desk', 'barcode' => '1049284'],
                    ['copy' => 'PY-005', 'status' => 'available', 'location' => '2nd Flr Shelves 2B', 'barcode' => '1049285'],
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
                'location' => 'Shelves 2B, Shelf 06',
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
                'location' => 'Shelves 2A, Shelf 01',
                'loan' => 'Standard 7-Day Student Loan',
                'policy' => 'circulating',
                'cover' => 'from-blue-800 to-indigo-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780262046305-M.jpg',
                'icon' => 'functions',
                'badge' => 'CLRS',
                'copies' => [
                    ['copy' => 'AL-001', 'status' => 'available', 'location' => 'Shelves 2A', 'barcode' => '3049201'],
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
                'location' => 'Shelves 3A, Shelf 05',
                'loan' => 'Standard 7-Day Student Loan',
                'policy' => 'circulating',
                'cover' => 'from-emerald-800 to-teal-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780134610993-M.jpg',
                'icon' => 'smart_toy',
                'badge' => '4th Ed',
                'copies' => [
                    ['copy' => 'AI-001', 'status' => 'available', 'location' => 'Shelves 3A', 'barcode' => '4049301'],
                    ['copy' => 'AI-002', 'status' => 'available', 'location' => 'Shelves 3A', 'barcode' => '4049302'],
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
                'location' => 'Shelves 2B, Shelf 06',
                'loan' => 'Standard 7-Day Student Loan',
                'policy' => 'circulating',
                'cover' => 'from-cyan-900 to-blue-950',
                'cover_url' => 'https://covers.openlibrary.org/b/isbn/9780073376257-M.jpg',
                'icon' => 'account_tree',
                'badge' => 'Agile',
                'copies' => [
                    ['copy' => 'SE-001', 'status' => 'available', 'location' => 'Shelves 2B', 'barcode' => '6049501'],
                    ['copy' => 'SE-002', 'status' => 'available', 'location' => 'Shelves 2B', 'barcode' => '6049502'],
                    ['copy' => 'SE-003', 'status' => 'available', 'location' => 'Shelves 2B', 'barcode' => '6049503'],
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
