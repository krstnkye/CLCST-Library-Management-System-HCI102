<?php

use App\Models\AuditLog;
use App\Models\Reservation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('library:expire-holds', function () {
    $expired = Reservation::query()
        ->where('status', 'ready')
        ->where('ready_until', '<', now())
        ->update(['status' => 'expired', 'closed_at' => now()]);

    if ($expired > 0) {
        AuditLog::record(null, 'reservation.expired', "Closed {$expired} pickup hold(s) that were not collected in time.");
    }

    $this->info("Expired {$expired} uncollected hold(s).");
})->purpose('Close reservations whose pickup period has passed');

Schedule::command('library:expire-holds')->dailyAt('00:05');
