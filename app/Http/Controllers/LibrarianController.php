<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\LibraryBook;
use App\Models\LibraryRequest;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use App\Services\CirculationService;
use App\Services\LibrarySettings;
use App\Support\LibraryReports;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The librarian workspace: catalog maintenance, the circulation desk, fines,
 * patron requests and announcements. Administrators and super administrators
 * can open it as well.
 */
class LibrarianController extends Controller
{
    public const PAGES = [
        'books' => ['Book Catalog', 'Search, review, and update library books.', 'menu_book'],
        'add-book' => ['Add New Book', 'Create a catalog record and its physical copies.', 'library_add'],
        'categories' => ['Book Categories', 'See how the collection is organized and how much of it is on loan.', 'category'],
        'inventory' => ['Book Inventory', 'Track every physical copy and mark copies for repair or as lost.', 'inventory_2'],
        'borrow-requests' => ['Check Out Books', 'Lend books at the desk and serve patrons who reserved a title.', 'outbox'],
        'returns' => ['Check In Returns', 'Receive returned books; overdue fines are charged automatically.', 'assignment_return'],
        'overdue' => ['Overdue Books', 'Loans past their due date and the fines they are adding.', 'schedule'],
        'reservations' => ['Reservations', 'Waiting lists and books held for pickup.', 'bookmark'],
        'fines' => ['Fines and Payments', 'Record cashier payments and approved waivers.', 'payments'],
        'feedback' => ['Requests and Feedback', 'Answer faculty requests, book suggestions, and patron concerns.', 'forum'],
        'announcements' => ['Announcements', 'Publish notices that appear in patron notifications.', 'campaign'],
        'reports' => ['Reports and Analytics', 'Circulation, collection, and fine summaries from live records.', 'monitoring'],
        'activity-logs' => ['Activity Logs', 'Every sign-in, account change, and circulation action.', 'history'],
        'settings' => ['Borrowing Policy', 'The rules every checkout and renewal follows.', 'rule'],
        'notifications' => ['Alerts', 'Items that need librarian attention today.', 'notifications'],
        'profile' => ['My Profile', 'Your account details and password.', 'account_circle'],
        'support' => ['Help and Support', 'How-to guides for the librarian workspace.', 'help'],
    ];

    public function __construct(private CirculationService $circulation, private LibrarySettings $settings)
    {
    }

    public function dashboard(): View
    {
        return view('librarian.dashboard', [
            'activePage' => 'dashboard',
            'stats' => $this->stats(),
            'dueToday' => Loan::active()->whereDate('due_at', today())->with(['user', 'copy.book'])->get(),
            'pendingReservations' => Reservation::open()->with(['user', 'book'])->oldest()->limit(5)->get(),
            'activities' => AuditLog::with('user')->where('event', 'like', 'loan.%')->orWhere('event', 'like', 'fine.%')->orWhere('event', 'like', 'reservation.%')->latest()->limit(6)->get(),
        ]);
    }

    public function section(Request $request, string $section): View|RedirectResponse
    {
        if (in_array($section, ['users', 'verification'], true)) {
            return redirect()->route('management.accounts.index');
        }
        abort_unless(isset(self::PAGES[$section]), 404);

        [$title, $description, $icon] = self::PAGES[$section];

        return view('librarian.section', array_merge([
            'activePage' => $section,
            'section' => $section,
            'page' => ['title' => $title, 'description' => $description, 'icon' => $icon],
            'stats' => $this->stats(),
            'policy' => $this->settings->all(),
        ], $this->sectionData($request, $section)));
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $data = $this->validateBook($request);
        $copies = (int) $request->validate(['copies' => ['required', 'integer', 'min:1', 'max:50']])['copies'];

        $book = DB::transaction(function () use ($data, $copies): LibraryBook {
            $book = LibraryBook::create($data + ['status' => 'active']);
            $book->addCopies($copies);

            return $book;
        });
        AuditLog::record($request->user(), 'book.created', 'Added "'.$book->title.'" with '.$copies.' copies.', ['book_id' => $book->id]);

        return redirect()->route('librarian.section', ['books', 'q' => $book->isbn])->with('status', '"'.$book->title.'" was added with '.$copies.' copies.');
    }

    public function editBook(LibraryBook $book): View
    {
        return view('librarian.section', [
            'activePage' => 'books',
            'section' => 'edit-book',
            'page' => ['title' => 'Edit Book', 'description' => 'Update the catalog record for "'.$book->title.'".', 'icon' => 'edit'],
            'stats' => $this->stats(),
            'book' => $book->load('copies.activeLoan.user'),
        ]);
    }

    public function updateBook(Request $request, LibraryBook $book): RedirectResponse
    {
        $data = $this->validateBook($request, $book);
        $data['status'] = $request->validate(['status' => ['required', Rule::in(['active', 'archived'])]])['status'];

        if ($data['status'] === 'archived' && $book->copies()->where('status', 'on_loan')->exists()) {
            return back()->withErrors(['status' => 'This title still has copies on loan. Check them in before archiving it.']);
        }

        $book->update($data);
        AuditLog::record($request->user(), 'book.updated', 'Updated the catalog record for "'.$book->title.'".', ['book_id' => $book->id]);

        return back()->with('status', 'Catalog record saved.');
    }

    public function storeCopies(Request $request, LibraryBook $book): RedirectResponse
    {
        $count = (int) $request->validate(['copies' => ['required', 'integer', 'min:1', 'max:50']])['copies'];
        DB::transaction(fn () => $book->addCopies($count));
        AuditLog::record($request->user(), 'book.copies_added', 'Added '.$count.' copies of "'.$book->title.'".', ['book_id' => $book->id]);

        return back()->with('status', $count.' new '.Str::plural('copy', $count).' added.');
    }

    public function updateCopy(Request $request, BookCopy $copy): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', Rule::in(['available', 'maintenance', 'lost'])]])['status'];
        if ($copy->status === 'on_loan') {
            return back()->withErrors(['status' => 'Copy '.$copy->barcode.' is on loan. Check it in first.']);
        }
        if ($copy->book->isReference() && $status === 'available') {
            $status = 'reference';
        }

        $copy->update(['status' => $status]);
        $copy->book->syncCopyCounts();
        AuditLog::record($request->user(), 'copy.status', 'Marked copy '.$copy->barcode.' as '.$status.'.', ['copy_id' => $copy->id]);

        return back()->with('status', 'Copy '.$copy->barcode.' is now '.str_replace('_', ' ', $status).'.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patron' => ['required', 'string', 'max:150'],
            'barcode' => ['required', 'string', 'max:32'],
        ]);

        try {
            $patron = $this->findPatron($data['patron']);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors())->with('error_title', 'Book not checked out');
        }
        $copy = BookCopy::where('barcode', trim($data['barcode']))->orWhere('copy_code', strtoupper(trim($data['barcode'])))->first();
        if (! $copy) {
            return back()->withInput()->withErrors(['barcode' => 'No copy matches barcode "'.$data['barcode'].'".'])->with('error_title', 'Book not checked out');
        }

        try {
            $loan = $this->circulation->checkout($request->user(), $patron, $copy);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors())->with('error_title', 'Book not checked out');
        }

        return back()->with('status_title', 'Book checked out')->with('status', 'Lent "'.$copy->book->title.'" to '.$patron->name.'. Due '.$loan->due_at->format('M d, Y').'. Receipt '.$loan->receipt_no.'.');
    }

    public function issueReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $copy = $reservation->book->copies()->where('status', 'available')->first();
        if (! $copy) {
            return back()->withErrors(['circulation' => 'No copy of "'.$reservation->book->title.'" is on the shelf right now.'])->with('error_title', 'Book not checked out');
        }

        try {
            $loan = $this->circulation->checkout($request->user(), $reservation->user, $copy);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->with('error_title', 'Book not checked out');
        }

        return back()->with('status_title', 'Book checked out')->with('status', 'Lent copy '.$copy->copy_code.' of "'.$reservation->book->title.'" to '.$reservation->user->name.'. Due '.$loan->due_at->format('M d, Y').'.');
    }

    public function readyReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->circulation->markReady($request->user(), $reservation);

        return back()->with('status_title', 'Patron notified')->with('status', $reservation->user->name.' was notified that "'.$reservation->book->title.'" is ready for pickup.');
    }

    public function cancelReservation(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->circulation->cancelReservation($request->user(), $reservation);

        return back()->with('status_title', 'Reservation cancelled')->with('status', 'The reservation was cancelled and the patron will no longer be held a copy.');
    }

    public function checkin(Request $request): RedirectResponse
    {
        $barcode = trim($request->validate(['barcode' => ['required', 'string', 'max:32']])['barcode']);
        $loan = Loan::active()->whereHas('copy', fn ($copy) => $copy->where('barcode', $barcode)->orWhere('copy_code', strtoupper($barcode)))->first();
        if (! $loan) {
            return back()->withInput()->withErrors(['barcode' => 'No book on loan matches "'.$barcode.'".'])->with('error_title', 'Book not checked in');
        }

        return $this->completeCheckin($request, $loan);
    }

    public function checkinLoan(Request $request, Loan $loan): RedirectResponse
    {
        return $this->completeCheckin($request, $loan);
    }

    public function settleFine(Request $request, Fine $fine): RedirectResponse
    {
        $outcome = $request->validate(['outcome' => ['required', Rule::in(['paid', 'waived'])]])['outcome'];
        $fine = $this->circulation->settleFine($request->user(), $fine, $outcome);

        return back()->with('status_title', $outcome === 'paid' ? 'Payment recorded' : 'Fine waived')->with('status', ($outcome === 'paid' ? 'Payment recorded' : 'Fine waived').' for '.$fine->user->name.'. Receipt '.$fine->receipt_no.'.');
    }

    public function updateRequest(Request $request, LibraryRequest $libraryRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(LibraryRequest::STATUSES)],
            'response' => ['nullable', 'string', 'max:2000'],
        ]);

        $libraryRequest->update($data + ['handled_by' => $request->user()->id]);
        AuditLog::record($request->user(), 'request.updated', 'Set request REQ-'.$libraryRequest->id.' to '.$libraryRequest->statusLabel().'.', ['request_id' => $libraryRequest->id]);

        return back()->with('status', 'Request REQ-'.str_pad((string) $libraryRequest->id, 5, '0', STR_PAD_LEFT).' updated.');
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:2000'],
            'audience' => ['required', Rule::in(array_keys(Announcement::AUDIENCES))],
        ]);

        $announcement = Announcement::create($data + ['user_id' => $request->user()->id, 'published_at' => now()]);
        AuditLog::record($request->user(), 'announcement.published', 'Published "'.$announcement->title.'".', ['announcement_id' => $announcement->id]);

        return back()->with('status_title', 'Announcement published')->with('status', 'Students and faculty can now see it in their notifications.');
    }

    public function destroyAnnouncement(Request $request, Announcement $announcement): RedirectResponse
    {
        $announcement->delete();
        AuditLog::record($request->user(), 'announcement.removed', 'Removed the announcement "'.$announcement->title.'".');

        return back()->with('status', 'Announcement removed.');
    }

    private function completeCheckin(Request $request, Loan $loan): RedirectResponse
    {
        $fine = $this->circulation->checkin($request->user(), $loan);
        $message = 'Checked in "'.$loan->copy->book->title.'" from '.$loan->user->name.'.';
        if ($fine) {
            $message .= ' Returned '.$fine->days_late.' day(s) late: a fine of PHP '.number_format((float) $fine->amount, 2).' was added to the account.';
        }
        $next = Reservation::open()->where('library_book_id', $loan->copy->library_book_id)->where('status', 'pending')->oldest()->first();
        if ($next) {
            $message .= ' '.$next->user->name.' is next on the waiting list.';
        }

        return back()->with('status_title', 'Book checked in')->with('status', $message);
    }

    private function sectionData(Request $request, string $section): array
    {
        $q = trim((string) $request->query('q', ''));

        return match ($section) {
            'books' => ['books' => LibraryBook::query()
                ->when($q !== '', fn ($query) => $query->where(function ($where) use ($q): void {
                    $where->where('title', 'like', "%{$q}%")->orWhere('author', 'like', "%{$q}%")->orWhere('isbn', 'like', "%{$q}%")->orWhere('call_number', 'like', "%{$q}%")
                        ->orWhereHas('copies', fn ($copy) => $copy->where('barcode', $q)->orWhere('copy_code', strtoupper($q)));
                    // Scanned ISBNs have no hyphens.
                    $digits = preg_replace('/[^0-9Xx]/', '', $q);
                    if (strlen($digits) >= 4) {
                        $where->orWhereRaw("REPLACE(isbn, '-', '') like ?", ['%'.$digits.'%']);
                    }
                }))
                ->when($request->query('status') === 'archived', fn ($query) => $query->where('status', 'archived'), fn ($query) => $query->where('status', 'active'))
                ->orderBy('title')->paginate(15)->withQueryString()],
            'add-book' => ['categories' => LibraryBook::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')],
            'categories' => ['categories' => LibraryBook::query()->where('status', 'active')
                ->select('category', DB::raw('count(*) as titles'), DB::raw('sum(total_copies) as copies'), DB::raw('sum(available_copies) as available'))
                ->groupBy('category')->orderBy('category')->get()],
            'inventory' => ['copies' => BookCopy::query()->with(['book', 'activeLoan.user'])
                ->when(in_array($request->query('status'), BookCopy::STATUSES, true), fn ($query) => $query->where('status', $request->query('status')))
                ->when($q !== '', fn ($query) => $query->where(fn ($where) => $where->where('barcode', 'like', "%{$q}%")->orWhere('copy_code', 'like', "%{$q}%")->orWhereHas('book', fn ($book) => $book->where('title', 'like', "%{$q}%"))))
                ->orderBy('library_book_id')->orderBy('copy_code')->paginate(25)->withQueryString(),
                'statusCounts' => BookCopy::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status')],
            'borrow-requests' => ['reservations' => Reservation::open()->with(['user', 'book' => fn ($book) => $book->withCount(['copies as available_count' => fn ($copy) => $copy->where('status', 'available')])])->oldest()->get(),
                'recentLoans' => Loan::with(['user', 'copy.book'])->latest('borrowed_at')->limit(8)->get()],
            'returns' => ['loans' => Loan::active()->with(['user', 'copy.book'])
                ->when($q !== '', fn ($query) => $query->where(fn ($where) => $where->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$q}%")->orWhere('patron_id', 'like', "%{$q}%"))->orWhereHas('copy', fn ($copy) => $copy->where('barcode', 'like', "%{$q}%"))))
                ->orderBy('due_at')->paginate(20)->withQueryString()],
            'overdue' => ['loans' => Loan::overdue()->with(['user', 'copy.book'])->orderBy('due_at')->get()],
            'reservations' => ['reservations' => Reservation::with(['user', 'book'])->orderByRaw("CASE status WHEN 'ready' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END")->latest()->paginate(25)],
            'fines' => ['outstanding' => Fine::with(['user', 'loan.copy.book'])->where('status', 'outstanding')->latest()->get(),
                'settled' => Fine::with(['user', 'settledBy'])->whereIn('status', ['paid', 'waived'])->latest('settled_at')->limit(15)->get()],
            'feedback' => ['requests' => LibraryRequest::with(['user', 'handler'])
                ->when(array_key_exists((string) $request->query('type'), LibraryRequest::TYPES), fn ($query) => $query->where('type', $request->query('type')))
                ->orderByRaw("CASE status WHEN 'submitted' THEN 0 WHEN 'in_review' THEN 1 ELSE 2 END")->latest()->paginate(15)->withQueryString()],
            'announcements' => ['announcements' => Announcement::with('author')->latest('published_at')->get()],
            'reports' => ['report' => LibraryReports::build()],
            'activity-logs' => ['logs' => AuditLog::with('user')
                ->when($q !== '', fn ($query) => $query->where(fn ($where) => $where->where('description', 'like', "%{$q}%")->orWhere('event', 'like', "%{$q}%")))
                ->latest()->paginate(30)->withQueryString()],
            'notifications' => ['alerts' => $this->alerts()],
            default => [],
        };
    }

    private function stats(): array
    {
        return [
            'titles' => LibraryBook::where('status', 'active')->count(),
            'availableCopies' => BookCopy::where('status', 'available')->count(),
            'borrowedBooks' => Loan::active()->count(),
            'overdueBooks' => Loan::overdue()->count(),
            'openReservations' => Reservation::open()->count(),
            'outstandingFines' => (float) Fine::where('status', 'outstanding')->sum('amount'),
            'registeredUsers' => User::whereIn('role', User::PATRON_ROLES)->where('status', 'active')->count(),
            'pendingAccounts' => User::where('status', 'pending')->count(),
            'openRequests' => LibraryRequest::whereIn('status', ['submitted', 'in_review'])->count(),
        ];
    }

    private function alerts(): array
    {
        $stats = $this->stats();
        $alerts = [];
        if ($stats['pendingAccounts'] > 0) {
            $alerts[] = ['how_to_reg', 'amber', $stats['pendingAccounts'].' account application(s) waiting for review.', route('management.accounts.index'), 'Review accounts'];
        }
        if ($stats['overdueBooks'] > 0) {
            $alerts[] = ['schedule', 'red', $stats['overdueBooks'].' book(s) are overdue.', route('librarian.section', 'overdue'), 'View overdue'];
        }
        $waiting = Reservation::where('status', 'pending')->whereHas('book.copies', fn ($copy) => $copy->where('status', 'available'))->count();
        if ($waiting > 0) {
            $alerts[] = ['bookmark', 'blue', $waiting.' reservation(s) can be filled from shelf copies.', route('librarian.section', 'borrow-requests'), 'Serve reservations'];
        }
        $expired = Reservation::where('status', 'ready')->where('ready_until', '<', now())->count();
        if ($expired > 0) {
            $alerts[] = ['event_busy', 'amber', $expired.' pickup hold(s) have passed their pickup date.', route('librarian.section', 'reservations'), 'Review holds'];
        }
        if ($stats['openRequests'] > 0) {
            $alerts[] = ['forum', 'blue', $stats['openRequests'].' patron request(s) need a response.', route('librarian.section', 'feedback'), 'Open requests'];
        }
        $repair = BookCopy::where('status', 'maintenance')->count();
        if ($repair > 0) {
            $alerts[] = ['build', 'slate', $repair.' cop(ies) are out for repair.', route('librarian.section', ['inventory', 'status' => 'maintenance']), 'View copies'];
        }

        return $alerts;
    }

    private function validateBook(Request $request, ?LibraryBook $book = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'publisher' => ['nullable', 'string', 'max:150'],
            'isbn' => ['required', 'string', 'max:32', 'regex:/^[0-9Xx\- ]{10,17}$/', Rule::unique('library_books', 'isbn')->ignore($book?->id)],
            'call_number' => ['nullable', 'string', 'max:80'],
            'category' => ['required', 'string', 'max:100'],
            'subject' => ['nullable', 'string', 'max:100'],
            'publication_year' => ['nullable', 'integer', 'min:1800', 'max:'.(now()->year + 1)],
            'shelf_location' => ['nullable', 'string', 'max:100'],
            'loan_policy' => ['required', Rule::in(['circulating', 'reference'])],
        ], ['isbn.regex' => 'Enter a 10 or 13 digit ISBN (digits and dashes only).']);
    }

    private function findPatron(string $identifier): User
    {
        $identifier = mb_strtolower(trim($identifier));
        $patron = User::query()
            ->whereRaw('LOWER(email) = ?', [$identifier])
            ->orWhereRaw('LOWER(patron_id) = ?', [$identifier])
            ->first();

        if (! $patron) {
            throw ValidationException::withMessages(['patron' => 'No account matches "'.$identifier.'".']);
        }

        return $patron;
    }
}
