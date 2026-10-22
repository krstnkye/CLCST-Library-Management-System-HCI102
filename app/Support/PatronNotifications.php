<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds a patron's notification feed from live records: loans that are due
 * or overdue, reservations ready for pickup, unpaid fines, request updates,
 * and published announcements.
 */
class PatronNotifications
{
    /** @return Collection<int, array<string, mixed>> */
    public static function for(User $user): Collection
    {
        $prefix = $user->role === 'student' ? 'student.section' : 'staff.section';
        $loansPage = $user->role === 'student' ? route('student.section', 'borrow-return') : route('staff.section', 'circulation');
        $items = collect();

        foreach ($user->loans()->active()->with('copy.book')->get() as $loan) {
            $days = $loan->daysRemaining();
            if ($days < 0) {
                $items->push(self::item('alert', 'error', 'warning', 'Overdue: '.$loan->copy->book->title,
                    'This book was due on '.$loan->due_at->format('M d, Y').' ('.abs($days).' day(s) ago). Current fine: PHP '.number_format($loan->accruedFine(), 2).'. Please return it to the library desk.',
                    $loan->due_at, $loansPage, 'View my loans'));
            } elseif ($days <= 3) {
                $items->push(self::item('due', 'warning', 'schedule', 'Due soon: '.$loan->copy->book->title,
                    'Return or renew by '.$loan->due_at->format('l, M d').($days === 0 ? ' (today)' : ' ('.$days.' day(s) left)').'.',
                    $loan->due_at->copy()->subDays(3), $loansPage, 'Renew or view'));
            }
        }

        foreach ($user->reservations()->where('status', 'ready')->with('book')->get() as $reservation) {
            $items->push(self::item('hold', 'success', 'local_library', 'Ready for pickup: '.$reservation->book->title,
                'Your reserved book is waiting at the circulation desk until '.$reservation->ready_until?->format('M d, Y').'.',
                $reservation->updated_at, route($prefix, 'reservations'), 'View reservation'));
        }

        $outstanding = $user->outstandingFineTotal();
        if ($outstanding > 0 && $user->role === 'student') {
            $items->push(self::item('fine', 'warning', 'payments', 'Unpaid fine: PHP '.number_format($outstanding, 2),
                'Settle your balance at the library cashier to keep borrowing privileges.',
                $user->fines()->where('status', 'outstanding')->max('created_at'), route('student.section', 'fines'), 'View fines'));
        }

        foreach ($user->libraryRequests()->whereColumn('updated_at', '>', 'created_at')->latest('updated_at')->limit(5)->get() as $request) {
            $items->push(self::item('request', 'info', 'forum', $request->typeLabel().' '.strtolower($request->statusLabel()),
                '"'.$request->subject.'"'.($request->response ? ' - Librarian note: '.$request->response : ''),
                $request->updated_at, null, null));
        }

        foreach (Announcement::query()->visibleTo($user->role)->latest('published_at')->limit(5)->get() as $announcement) {
            $items->push(self::item('announcement', 'info', 'campaign', $announcement->title, $announcement->body,
                $announcement->published_at, null, null));
        }

        return $items->sortByDesc('at')->values();
    }

    /** Count of items that need the patron to act (excludes general news). */
    public static function actionableCount(User $user): int
    {
        return self::for($user)->whereNotIn('type', ['announcement', 'request'])->count();
    }

    private static function item(string $type, string $tone, string $icon, string $title, string $body, $at, ?string $href, ?string $action): array
    {
        return [
            'type' => $type,
            'tone' => $tone,
            'icon' => $icon,
            'title' => $title,
            'body' => $body,
            'at' => $at ? Carbon::parse($at) : now(),
            'href' => $href,
            'action' => $action,
        ];
    }
}
