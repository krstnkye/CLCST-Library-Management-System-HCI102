<?php

namespace App\Support;

use App\Http\Controllers\PatronController;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Builds a patron's notification feed from live records: loans that are due
 * or overdue, reservations ready for pickup, unpaid fines, request updates,
 * and published announcements. Each item has a stable key so the patron
 * can mark it read or unread (stored in notification_reads).
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
                    $loan->due_at, $loansPage, 'View my loans', 'loan-overdue-'.$loan->id));
            } elseif ($days <= 3) {
                $items->push(self::item('due', 'warning', 'schedule', 'Due soon: '.$loan->copy->book->title,
                    'Return or renew by '.$loan->due_at->format('l, M d').($days === 0 ? ' (today)' : ' ('.$days.' day(s) left)').'.',
                    $loan->due_at->copy()->subDays(3), $loansPage, 'Renew or view', 'loan-due-'.$loan->id.'-'.$loan->due_at->format('Ymd')));
            }
        }

        foreach ($user->reservations()->where('status', 'ready')->with('book')->get() as $reservation) {
            $items->push(self::item('hold', 'success', 'local_library', 'Ready for pickup: '.$reservation->book->title,
                'Your reserved book is waiting at the circulation desk until '.$reservation->ready_until?->format('M d, Y').'.',
                $reservation->updated_at, route($prefix, 'reservations'), 'View reservation', 'hold-'.$reservation->id));
        }

        $outstanding = $user->outstandingFineTotal();
        if ($outstanding > 0 && $user->role === 'student') {
            $items->push(self::item('fine', 'warning', 'payments', 'Unpaid fine: PHP '.number_format($outstanding, 2),
                'Settle your balance at the library cashier to keep borrowing privileges.',
                $user->fines()->where('status', 'outstanding')->max('created_at'), route('student.section', 'fines'), 'View fines',
                'fine-'.$user->fines()->where('status', 'outstanding')->max('id')));
        }

        foreach ($user->libraryRequests()->whereColumn('updated_at', '>', 'created_at')->latest('updated_at')->limit(5)->get() as $request) {
            $items->push(self::item('request', 'info', 'forum', $request->typeLabel().' '.strtolower($request->statusLabel()),
                '"'.$request->subject.'"'.($request->response ? ' - Librarian note: '.$request->response : ''),
                $request->updated_at, route($prefix, $user->role === 'student' ? 'support' : (array_search($request->type, PatronController::FACULTY_REQUEST_PAGES, true) ?: 'support')), 'View my requests', 'request-'.$request->id.'-'.$request->updated_at->timestamp));
        }

        foreach (Announcement::query()->visibleTo($user->role)->latest('published_at')->limit(5)->get() as $announcement) {
            $items->push(self::item('announcement', 'info', 'campaign', $announcement->title, $announcement->body,
                $announcement->published_at, null, null, 'announcement-'.$announcement->id));
        }

        $read = DB::table('notification_reads')->where('user_id', $user->id)->pluck('read_at', 'notification_key');

        return $items->map(fn (array $item) => $item + ['read' => $read->has($item['key'])])->sortByDesc('at')->values();
    }

    /** Notifications the patron has not opened or marked as read yet. */
    public static function unreadCount(User $user): int
    {
        return self::for($user)->where('read', false)->count();
    }

    /** @param  array<int, string>  $keys */
    public static function markRead(User $user, array $keys): int
    {
        $now = now();

        return DB::table('notification_reads')->insertOrIgnore(array_map(
            fn (string $key) => ['user_id' => $user->id, 'notification_key' => $key, 'read_at' => $now],
            array_values(array_unique($keys)),
        ));
    }

    /** @param  array<int, string>|null  $keys  null marks every notification unread */
    public static function markUnread(User $user, ?array $keys = null): int
    {
        return DB::table('notification_reads')->where('user_id', $user->id)
            ->when($keys !== null, fn ($query) => $query->whereIn('notification_key', $keys))
            ->delete();
    }

    private const TYPE_LABELS = [
        'alert' => 'Overdue',
        'due' => 'Due soon',
        'hold' => 'Pickup',
        'fine' => 'Fine',
        'request' => 'Request update',
        'announcement' => 'Announcement',
    ];

    private static function item(string $type, string $tone, string $icon, string $title, string $body, $at, ?string $href, ?string $action, string $key): array
    {
        return [
            'key' => $key,
            'type' => $type,
            'label' => self::TYPE_LABELS[$type],
            'group' => match ($type) { 'announcement' => 'announcements', 'request' => 'updates', default => 'alerts' },
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
