<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\LibraryBook;
use App\Models\LibraryRequest;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use App\Services\CirculationService;
use App\Services\LibrarySettings;
use App\Support\PatronNotifications;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Pages and self-service actions for students and faculty (library patrons).
 */
class PatronController extends Controller
{
    public const STUDENT_SECTIONS = [
        'borrow-return' => 'My Loans',
        'reservations' => 'Reservations',
        'history' => 'Borrowing History',
        'fines' => 'Fines & Penalties',
        'notifications' => 'Notifications',
        'account' => 'My Account',
        'support' => 'Help & Support',
        'circulation-policy' => 'Borrowing Policy',
    ];

    public const FACULTY_SECTIONS = [
        'circulation' => 'My Borrowed Books',
        'reservations' => 'My Reservations',
        'history' => 'Borrowing History',
        'notifications' => 'Notifications',
        'catalog' => 'Recommend a Book',
        'course-reserves' => 'Course Reserves',
        'reading-lists' => 'Reading Lists',
        'instruction-sessions' => 'Library Class Sessions',
        'inter-library-loan' => 'Inter-Library Loan',
        'academic' => 'Academic Requests',
        'feedback' => 'Library Feedback',
        'profile' => 'My Profile',
        'support' => 'Help & Support',
    ];

    /** Which request type each faculty request page submits. */
    public const FACULTY_REQUEST_PAGES = [
        'catalog' => 'book_recommendation',
        'course-reserves' => 'course_reserve',
        'reading-lists' => 'reading_list',
        'instruction-sessions' => 'instruction_session',
        'inter-library-loan' => 'inter_library_loan',
        'academic' => 'academic',
        'feedback' => 'feedback',
        'support' => 'support',
    ];

    public function __construct(private CirculationService $circulation, private LibrarySettings $settings)
    {
    }

    public function studentDashboard(Request $request): View
    {
        $user = $request->user();

        return view('student.dashboard', array_merge($this->patronData($user), [
            'featuredBooks' => LibraryBook::query()->where('status', 'active')->withCount(['copies as available_count' => fn ($q) => $q->where('status', 'available')])->orderByDesc('available_count')->limit(4)->get(),
            'returnedCount' => $user->loans()->whereNotNull('returned_at')->count(),
        ]));
    }

    public function studentSection(Request $request, string $section): View|StreamedResponse
    {
        abort_unless(isset(self::STUDENT_SECTIONS[$section]), 404);
        $user = $request->user();

        if ($section === 'history' && $request->query('export') === 'csv') {
            return $this->exportHistory($user);
        }

        return view('student.'.$section, array_merge($this->patronData($user), [
            'title' => self::STUDENT_SECTIONS[$section],
            'section' => $section,
        ], $this->sectionData($user, $section)));
    }

    public function facultyDashboard(Request $request): View
    {
        $user = $request->user();

        return view('staff.dashboard', array_merge($this->patronData($user), [
            'activePage' => 'dashboard',
            'requests' => $user->libraryRequests()->latest()->limit(5)->get(),
        ]));
    }

    public function facultySection(Request $request, string $section): View|StreamedResponse
    {
        abort_unless(isset(self::FACULTY_SECTIONS[$section]), 404);
        $user = $request->user();

        if ($section === 'history' && $request->query('export') === 'csv') {
            return $this->exportHistory($user);
        }

        $requestType = self::FACULTY_REQUEST_PAGES[$section] ?? null;

        return view('staff.section', array_merge($this->patronData($user), [
            'title' => self::FACULTY_SECTIONS[$section],
            'section' => $section,
            'activePage' => $section,
            'requestType' => $requestType,
            'requests' => $requestType ? $user->libraryRequests()->where('type', $requestType)->latest()->get() : collect(),
        ], $this->sectionData($user, $section)));
    }

    public function catalog(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $field = (string) $request->query('field', 'all');
        $category = (string) $request->query('category', 'all');
        $availability = (string) $request->query('availability', 'all');
        $sort = (string) $request->query('sort', 'relevance');

        $books = LibraryBook::query()
            ->where('status', 'active')
            ->with('copies.activeLoan')
            ->withCount(['copies as available_count' => fn ($q) => $q->where('status', 'available')])
            ->when($query !== '', function ($builder) use ($query, $field): void {
                $like = '%'.$query.'%';
                $builder->where(function ($where) use ($like, $field): void {
                    match ($field) {
                        'title' => $where->where('title', 'like', $like),
                        'author' => $where->where('author', 'like', $like),
                        'isbn' => $where->where('isbn', 'like', $like),
                        'call' => $where->where('call_number', 'like', $like),
                        default => $where->where('title', 'like', $like)
                            ->orWhere('author', 'like', $like)
                            ->orWhere('isbn', 'like', $like)
                            ->orWhere('call_number', 'like', $like)
                            ->orWhere('subject', 'like', $like)
                            ->orWhere('category', 'like', $like)
                            ->orWhereHas('copies', fn ($copy) => $copy->where('barcode', 'like', $like)),
                    };
                });
            })
            ->when($category !== 'all', fn ($builder) => $builder->where('category', $category))
            ->when($availability === 'available', fn ($builder) => $builder->whereHas('copies', fn ($copy) => $copy->where('status', 'available')))
            ->when($availability === 'reference', fn ($builder) => $builder->where('loan_policy', 'reference'));

        match ($sort) {
            'title' => $books->orderBy('title'),
            'newest' => $books->orderByDesc('publication_year'),
            'available' => $books->orderByDesc('available_count'),
            default => $books->orderBy('title'),
        };

        $books = $books->paginate(8)->withQueryString();
        $selectedId = $request->query('selected');
        $selectedBook = (is_numeric($selectedId) ? LibraryBook::with('copies.activeLoan')->where('status', 'active')->find($selectedId) : null)
            ?? $books->first();

        return view('books.catalog', [
            'books' => $books,
            'selectedBook' => $selectedBook,
            'categories' => LibraryBook::query()->where('status', 'active')->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
            'reservedBookIds' => $request->user()->reservations()->open()->pluck('library_book_id')->all(),
            'borrowedBookIds' => $request->user()->loans()->active()->with('copy')->get()->pluck('copy.library_book_id')->all(),
            'totalTitles' => LibraryBook::where('status', 'active')->count(),
            'query' => $query,
            'field' => $field,
            'category' => $category,
            'availability' => $availability,
            'sort' => $sort,
        ]);
    }

    public function reserve(Request $request, LibraryBook $book): RedirectResponse
    {
        $this->circulation->reserve($request->user(), $book);

        return back()->with('status', 'Reservation placed for "'.$book->title.'". We will notify you when it is ready for pickup.');
    }

    public function cancelReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        abort_unless($reservation->user_id === $request->user()->id, 403);
        $this->circulation->cancelReservation($request->user(), $reservation);

        return back()->with('status', 'Reservation cancelled for "'.$reservation->book->title.'".');
    }

    public function renew(Request $request, Loan $loan): RedirectResponse
    {
        $loan = $this->circulation->renew($request->user(), $loan);

        return back()->with('status', 'Renewed "'.$loan->copy->book->title.'". New due date: '.$loan->due_at->format('M d, Y').'.');
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $user = $request->user();
        $allowed = $user->role === 'faculty' ? array_values(self::FACULTY_REQUEST_PAGES) : ['support', 'feedback', 'book_recommendation'];

        $validated = $request->validate([
            'type' => ['required', Rule::in($allowed)],
            'subject' => ['required', 'string', 'max:200'],
            'details' => ['nullable', 'string', 'max:3000'],
        ]);

        $libraryRequest = $user->libraryRequests()->create($validated + ['status' => 'submitted']);
        AuditLog::record($user, 'request.submitted', 'Submitted a '.strtolower($libraryRequest->typeLabel()).': '.$libraryRequest->subject);

        return back()->with('status', $libraryRequest->typeLabel().' sent to the library. Reference no. REQ-'.str_pad((string) $libraryRequest->id, 5, '0', STR_PAD_LEFT).'.');
    }

    /** Numbers and records most patron pages share. */
    private function patronData(User $user): array
    {
        $activeLoans = $user->loans()->active()->with('copy.book')->orderBy('due_at')->get();

        return [
            'patron' => $user,
            'activeLoans' => $activeLoans,
            'openReservations' => $user->reservations()->open()->with('book')->oldest()->get(),
            'outstandingFines' => $user->outstandingFineTotal(),
            'loanLimit' => $this->settings->maxLoansFor($user->role),
            'loanDays' => $this->settings->loanDaysFor($user->role),
            'policy' => $this->settings->all(),
            'notifications' => PatronNotifications::for($user),
        ];
    }

    private function sectionData(User $user, string $section): array
    {
        return match ($section) {
            'history', 'circulation' => [
                'history' => $user->loans()->with(['copy.book', 'fine', 'issuer'])->latest('borrowed_at')->get(),
            ],
            'fines', 'account' => [
                'fines' => $user->fines()->with(['loan.copy.book', 'settledBy'])->latest()->get(),
                'history' => $user->loans()->with('copy.book')->latest('borrowed_at')->get(),
            ],
            'reservations' => [
                'reservationHistory' => $user->reservations()->whereNotIn('status', Reservation::OPEN_STATUSES)->with('book')->latest()->limit(10)->get(),
            ],
            'support' => [
                'requests' => $user->libraryRequests()->latest()->limit(10)->get(),
            ],
            'notifications' => [
                'announcements' => Announcement::query()->visibleTo($user->role)->latest('published_at')->limit(10)->get(),
            ],
            default => [],
        };
    }

    private function exportHistory(User $user): StreamedResponse
    {
        $loans = $user->loans()->with(['copy.book', 'fine'])->latest('borrowed_at')->get();

        return response()->streamDownload(function () use ($loans): void {
            $out = fopen('php://output', 'wb');
            fputcsv($out, ['Receipt', 'Title', 'Barcode', 'Borrowed', 'Due', 'Returned', 'Fine (PHP)', 'Fine status']);
            foreach ($loans as $loan) {
                fputcsv($out, [
                    $loan->receipt_no,
                    $loan->copy->book->title,
                    $loan->copy->barcode,
                    $loan->borrowed_at->format('Y-m-d'),
                    $loan->due_at->format('Y-m-d'),
                    $loan->returned_at?->format('Y-m-d') ?? 'On loan',
                    $loan->fine ? number_format((float) $loan->fine->amount, 2) : '0.00',
                    $loan->fine?->status ?? '',
                ]);
            }
            fclose($out);
        }, 'borrowing-history-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
