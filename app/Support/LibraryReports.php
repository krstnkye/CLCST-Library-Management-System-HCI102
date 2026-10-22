<?php

namespace App\Support;

use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\LibraryBook;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Summary figures for the Reports pages, computed from live records.
 */
class LibraryReports
{
    public static function build(): array
    {
        $since = now()->subMonths(5)->startOfMonth();
        $loansByMonth = Loan::query()->where('borrowed_at', '>=', $since)->get(['borrowed_at'])
            ->groupBy(fn (Loan $loan) => $loan->borrowed_at->format('Y-m'));
        $months = collect(range(5, 0))->map(function (int $ago) use ($loansByMonth): array {
            $month = now()->subMonths($ago);

            return ['label' => $month->format('M Y'), 'count' => $loansByMonth->get($month->format('Y-m'))?->count() ?? 0];
        });

        $returned = Loan::query()->whereNotNull('returned_at')->get(['due_at', 'returned_at']);
        $onTime = $returned->filter(fn (Loan $loan) => $loan->returned_at->copy()->startOfDay()->lte($loan->due_at->copy()->startOfDay()))->count();

        return [
            'totals' => [
                'Catalog titles' => LibraryBook::where('status', 'active')->count(),
                'Physical copies' => BookCopy::whereNot('status', 'lost')->count(),
                'Loans recorded' => Loan::count(),
                'Active patrons' => User::whereIn('role', User::PATRON_ROLES)->where('status', 'active')->count(),
            ],
            'months' => $months,
            'maxMonth' => max(1, $months->max('count')),
            'onTimeRate' => $returned->count() > 0 ? round($onTime / $returned->count() * 100) : null,
            'topTitles' => LibraryBook::query()
                ->join('book_copies', 'book_copies.library_book_id', '=', 'library_books.id')
                ->join('loans', 'loans.book_copy_id', '=', 'book_copies.id')
                ->select('library_books.title', DB::raw('count(loans.id) as loans'))
                ->groupBy('library_books.id', 'library_books.title')
                ->orderByDesc('loans')->limit(5)->get(),
            'byRole' => Loan::query()->join('users', 'users.id', '=', 'loans.user_id')
                ->select('users.role', DB::raw('count(*) as total'))->groupBy('users.role')->pluck('total', 'role'),
            'fines' => [
                'Collected' => (float) Fine::where('status', 'paid')->sum('amount'),
                'Waived' => (float) Fine::where('status', 'waived')->sum('amount'),
                'Outstanding' => (float) Fine::where('status', 'outstanding')->sum('amount'),
            ],
            'copyStatus' => BookCopy::query()->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
        ];
    }
}
