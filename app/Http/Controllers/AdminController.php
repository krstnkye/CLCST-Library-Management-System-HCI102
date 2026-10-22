<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\LibraryBook;
use App\Models\LibraryRequest;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use App\Services\LibrarySettings;
use App\Support\LibraryReports;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The administrator and super administrator portals.
 */
class AdminController extends Controller
{
    public const ADMIN_PAGES = [
        'users' => ['User Accounts', 'Find, review, and update library user accounts.', 'group'],
        'approvals' => ['Account Approvals', 'Review identity documents before activating accounts.', 'how_to_reg'],
        'books' => ['Book Records', 'Catalog records and copy counts.', 'menu_book'],
        'circulation' => ['Library Transactions', 'Every loan, return, and renewal.', 'sync_alt'],
        'reports' => ['Reports', 'Summaries of library use and collection activity.', 'bar_chart'],
        'audit-logs' => ['Activity Log', 'Security and data-management actions.', 'history'],
        'profile' => ['My Profile', 'Your administrator account details and password.', 'account_circle'],
    ];

    public const SUPER_PAGES = [
        'roles' => ['Roles and Permissions', 'What each account type can see and manage.', 'admin_panel_settings'],
        'borrowing-rules' => ['Borrowing Rules', 'Loan limits, loan periods, renewals, and reservations for each role.', 'rule'],
        'fine-settings' => ['Fine Settings', 'Overdue charges and the maximum fine per book.', 'payments'],
        'system-policies' => ['System Policies', 'Registration, password, and sign-in rules enforced by the system.', 'policy'],
        'reports' => ['Reports and Analytics', 'System-wide circulation, collection, and fine reports.', 'monitoring'],
        'security' => ['Security Management', 'Sign-in monitoring and failed attempts.', 'security'],
        'audit-logs' => ['Audit Logs', 'Every sign-in, account change, and circulation action.', 'history'],
        'maintenance' => ['System Status', 'Application, database, and storage health.', 'build'],
        'profile' => ['Super Admin Profile', 'Your account details and password.', 'account_circle'],
    ];

    public function __construct(private LibrarySettings $settings)
    {
    }

    public function adminDashboard(): View
    {
        return view('admin.dashboard', array_merge($this->overview(), ['activePage' => 'dashboard']));
    }

    public function adminSection(Request $request, string $section): View|RedirectResponse
    {
        if ($section === 'bulk-data') {
            return redirect()->route('management.data.index');
        }
        if ($section === 'announcements') {
            return redirect()->route('librarian.section', 'announcements');
        }
        abort_unless(isset(self::ADMIN_PAGES[$section]), 404);
        [$title, $description, $icon] = self::ADMIN_PAGES[$section];

        return view('admin.section', array_merge($this->overview(), [
            'activePage' => $section,
            'section' => $section,
            'page' => compact('title', 'description', 'icon'),
        ], $this->sectionData($request, $section)));
    }

    public function superAdminDashboard(): View
    {
        return view('superadmin.dashboard', array_merge($this->overview(), [
            'activePage' => 'dashboard',
            'systemActivities' => AuditLog::with('user')->latest()->limit(8)->get(),
            'failedLogins' => AuditLog::where('event', 'login.failed')->where('created_at', '>=', now()->subDay())->count(),
        ]));
    }

    public function superAdminSection(Request $request, string $section): View|RedirectResponse
    {
        $redirects = [
            'users' => route('management.accounts.index'),
            'approvals' => route('management.accounts.index', ['status' => 'pending']),
            'librarians' => route('management.accounts.index', ['role' => 'librarian']),
            'library-staff' => route('management.accounts.index', ['role' => 'admin']),
            'data-export' => route('management.data.index'),
            'announcements' => route('librarian.section', 'announcements'),
            'circulation' => route('librarian.dashboard'),
        ];
        if (isset($redirects[$section])) {
            return redirect($redirects[$section]);
        }
        abort_unless(isset(self::SUPER_PAGES[$section]), 404);
        [$title, $description, $icon] = self::SUPER_PAGES[$section];

        return view('superadmin.section', array_merge($this->overview(), [
            'activePage' => $section,
            'section' => $section,
            'page' => compact('title', 'description', 'icon'),
        ], $this->sectionData($request, $section)));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $rules = array_intersect_key(LibrarySettings::EDITABLE, $request->only(array_keys(LibrarySettings::EDITABLE)));
        abort_if($rules === [], 422, 'No settings were submitted.');

        $values = $request->validate($rules);
        $before = $this->settings->all();
        $this->settings->save($values, $request->user()->id);

        $changes = collect($values)->filter(fn ($value, $key) => (string) $before[$key] !== (string) $value)
            ->map(fn ($value, $key) => $before[$key].' -> '.$value)->all();
        AuditLog::record($request->user(), 'settings.updated', 'Changed circulation settings: '.($changes ? implode(', ', array_keys($changes)) : 'no changes').'.', $changes);

        return back()->with('status', 'Settings saved. New checkouts and renewals use them right away.');
    }

    private function overview(): array
    {
        return [
            'stats' => [
                'users' => User::count(),
                'staff' => User::whereIn('role', User::STAFF_ROLES)->count(),
                'patrons' => User::whereIn('role', User::PATRON_ROLES)->count(),
                'pending' => User::where('status', 'pending')->count(),
                'books' => LibraryBook::where('status', 'active')->count(),
                'copies' => BookCopy::whereNot('status', 'lost')->count(),
                'activeLoans' => Loan::active()->count(),
                'overdue' => Loan::overdue()->count(),
                'outstandingFines' => (float) Fine::where('status', 'outstanding')->sum('amount'),
                'openRequests' => LibraryRequest::whereIn('status', ['submitted', 'in_review'])->count(),
                'events' => AuditLog::count(),
            ],
            'recentUsers' => User::query()->latest()->limit(6)->get(),
            'recentActivity' => AuditLog::with('user')->latest()->limit(8)->get(),
        ];
    }

    private function sectionData(Request $request, string $section): array
    {
        $q = trim((string) $request->query('q', ''));

        return match ($section) {
            'users', 'approvals' => ['users' => User::query()
                ->whereIn('role', $request->user()->manageableRoles())
                ->when($section === 'approvals', fn ($query) => $query->where('status', 'pending'))
                ->when($q !== '', fn ($query) => $query->where(fn ($where) => $where->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")->orWhere('patron_id', 'like', "%{$q}%")))
                ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'active' THEN 1 ELSE 2 END")->orderBy('name')
                ->paginate(25)->withQueryString()],
            'books' => ['storedBooks' => LibraryBook::query()
                ->when($q !== '', fn ($query) => $query->where(fn ($where) => $where->where('title', 'like', "%{$q}%")->orWhere('isbn', 'like', "%{$q}%")))
                ->orderBy('title')->paginate(25)->withQueryString()],
            'circulation' => ['loans' => Loan::with(['user', 'copy.book', 'issuer'])->latest('borrowed_at')->paginate(25)],
            'reports' => ['report' => LibraryReports::build()],
            'audit-logs' => ['logs' => AuditLog::with('user')
                ->when($q !== '', fn ($query) => $query->where(fn ($where) => $where->where('description', 'like', "%{$q}%")->orWhere('event', 'like', "%{$q}%")))
                ->latest()->paginate(30)->withQueryString()],
            'borrowing-rules', 'fine-settings' => ['policy' => $this->settings->all()],
            'system-policies' => ['policy' => $this->settings->all()],
            'roles' => ['roleCounts' => User::query()->select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role')],
            'security' => [
                'failed' => AuditLog::where('event', 'login.failed')->where('created_at', '>=', now()->subDays(7))->latest()->limit(20)->get(),
                'signIns' => AuditLog::with('user')->where('event', 'login.succeeded')->latest()->limit(10)->get(),
                'suspended' => User::where('status', 'suspended')->count(),
            ],
            'maintenance' => ['system' => $this->systemStatus()],
            default => [],
        };
    }

    private function systemStatus(): array
    {
        $connection = DB::connection();
        $tables = ['users', 'library_books', 'book_copies', 'loans', 'reservations', 'fines', 'library_requests', 'announcements', 'audit_logs'];

        return [
            'environment' => [
                'Application' => config('app.name'),
                'Environment' => app()->environment(),
                'Debug mode' => config('app.debug') ? 'On (turn off before deployment)' : 'Off',
                'Laravel' => app()->version(),
                'PHP' => PHP_VERSION,
                'Database driver' => $connection->getDriverName(),
                'Session driver' => config('session.driver').(config('session.encrypt') ? ' (encrypted)' : ''),
                'Timezone' => config('app.timezone'),
            ],
            'tables' => collect($tables)->mapWithKeys(fn ($table) => [$table => DB::table($table)->count()])->all(),
            'expiredHolds' => Reservation::where('status', 'ready')->where('ready_until', '<', now())->count(),
        ];
    }
}
