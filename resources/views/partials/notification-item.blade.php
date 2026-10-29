@php
    $compact = $compact ?? false;
    $read = $notification['read'] ?? false;
@endphp
<article class="nc-item nc-tone-{{ $notification['tone'] }} {{ $compact ? 'nc-compact' : '' }} {{ $read ? 'is-read' : 'is-unread' }}"
    data-notification data-key="{{ $notification['key'] }}" data-group="{{ $notification['group'] }}"
    data-read-url="{{ route('notifications.read', $notification['key']) }}" data-unread-url="{{ route('notifications.unread', $notification['key']) }}">
    <button class="nc-open" type="button" data-notification-open aria-haspopup="dialog" aria-label="Open notification: {{ $notification['title'] }}">
        <span class="nc-icon material-symbols-outlined" aria-hidden="true">{{ $notification['icon'] }}</span>
        <span class="nc-content">
            <span class="nc-meta">
                <span class="nc-chip">{{ $notification['label'] }}</span>
                <time datetime="{{ $notification['at']->toIso8601String() }}" title="{{ $notification['at']->format('M d, Y g:i A') }}">{{ $notification['at']->diffForHumans() }}</time>
                <span class="nc-state" data-notification-state>{{ $read ? 'Read' : 'New' }}</span>
            </span>
            <span class="nc-title">{{ $notification['title'] }}</span>
            <span class="nc-preview">{{ $notification['body'] }}</span>
        </span>
        <span class="nc-expand material-symbols-outlined" aria-hidden="true">open_in_full</span>
    </button>
    @unless ($compact)
        <form class="nc-toggle" method="POST" action="{{ $read ? route('notifications.unread', $notification['key']) : route('notifications.read', $notification['key']) }}" data-notification-toggle>
            @csrf
            <button type="submit" title="{{ $read ? 'Mark as unread' : 'Mark as read' }}" aria-label="{{ $read ? 'Mark as unread' : 'Mark as read' }}">
                <span class="material-symbols-outlined" aria-hidden="true">{{ $read ? 'mark_email_unread' : 'mark_email_read' }}</span>
            </button>
        </form>
    @endunless
    <template data-notification-detail>
        <div class="nc-detail">
            <p class="nc-detail-when"><span class="material-symbols-outlined" aria-hidden="true">schedule</span>{{ $notification['at']->format('l, F j, Y \a\t g:i A') }} <span>({{ $notification['at']->diffForHumans() }})</span></p>
            <p class="nc-detail-body">{{ $notification['body'] }}</p>
            @if ($notification['href'])
                <a class="nc-detail-link" href="{{ $notification['href'] }}">{{ $notification['action'] }}<span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span></a>
            @endif
        </div>
    </template>
</article>
