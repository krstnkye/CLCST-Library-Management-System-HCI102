@php
    $unread = $notifications->where('read', false)->count();
    $total = $notifications->count();
    $groups = [
        'all' => ['All', 'inbox', $total],
        'unread' => ['Unread', 'mark_email_unread', $unread],
        'alerts' => ['Due dates & fines', 'notification_important', $notifications->where('group', 'alerts')->count()],
        'updates' => ['Request updates', 'forum', $notifications->where('group', 'updates')->count()],
        'announcements' => ['Announcements', 'campaign', $notifications->where('group', 'announcements')->count()],
    ];
    // Group the feed by day so long lists stay easy to scan.
    $byDay = $notifications->groupBy(function ($item) {
        $at = $item['at'];

        return match (true) {
            $at->isToday() || $at->isFuture() => 'Today',
            $at->isYesterday() => 'Yesterday',
            $at->greaterThan(now()->subDays(7)) => 'Earlier this week',
            default => 'Older',
        };
    });
@endphp

<section class="nc-center" data-notification-center aria-label="Notifications">
    <div class="nc-toolbar">
        <div class="nc-summary">
            <div class="nc-ring" style="--nc-progress: {{ $total > 0 ? round(($total - $unread) / $total * 100) : 100 }}%" aria-hidden="true">
                <span class="material-symbols-outlined">{{ $unread > 0 ? 'notifications_active' : 'notifications' }}</span>
            </div>
            <div>
                <p class="nc-summary-title"><span data-unread-count>{{ $unread }}</span> unread</p>
                <p class="nc-summary-sub">{{ $total }} {{ \Illuminate\Support\Str::plural('notification', $total) }} in your inbox. Select one to read it in full.</p>
            </div>
        </div>
        <div class="nc-bulk" role="group" aria-label="Mark all notifications">
            <form method="POST" action="{{ route('notifications.read-all') }}" data-notification-bulk="read">
                @csrf
                <button class="nc-bulk-read" type="submit" @disabled($unread === 0)>
                    <span class="material-symbols-outlined" aria-hidden="true">done_all</span>
                    <span>Mark all as read</span>
                </button>
            </form>
            <form method="POST" action="{{ route('notifications.unread-all') }}" data-notification-bulk="unread">
                @csrf
                <button class="nc-bulk-unread" type="submit" @disabled($total === 0 || $unread === $total)>
                    <span class="material-symbols-outlined" aria-hidden="true">mark_email_unread</span>
                    <span>Mark all as unread</span>
                </button>
            </form>
        </div>
    </div>

    <div class="nc-filters" role="tablist" aria-label="Filter notifications">
        @foreach ($groups as $value => [$label, $icon, $count])
            @continue($value !== 'all' && $value !== 'unread' && $count === 0)
            <button class="nc-filter" type="button" role="tab" data-notification-filter="{{ $value }}" aria-selected="{{ $value === 'all' ? 'true' : 'false' }}">
                <span class="material-symbols-outlined" aria-hidden="true">{{ $icon }}</span>{{ $label }}<span class="nc-filter-count" @if ($value === 'unread') data-unread-count @endif>{{ $count }}</span>
            </button>
        @endforeach
    </div>

    @forelse ($byDay as $day => $items)
        <div class="nc-day" data-notification-day>
            <h2 class="nc-day-label">{{ $day }}</h2>
            <div class="nc-list">
                @foreach ($items as $notification)
                    @include('partials.notification-item', ['notification' => $notification])
                @endforeach
            </div>
        </div>
    @empty
        <div class="nc-empty">
            <span class="material-symbols-outlined" aria-hidden="true">mark_email_read</span>
            <p class="font-bold">You are all caught up</p>
            <p>Due dates, pickups, fines and library announcements will appear here.</p>
        </div>
    @endforelse
    <div class="nc-empty" data-notification-filter-empty hidden>
        <span class="material-symbols-outlined" aria-hidden="true">filter_alt_off</span>
        <p class="font-bold">Nothing here</p>
        <p>No notifications match this filter.</p>
    </div>
</section>
