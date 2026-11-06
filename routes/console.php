<?php

use App\Models\Reservation;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('library:expire-holds', function () {
    $expired = Reservation::expireUncollectedHolds();

    $this->info("Expired {$expired} uncollected hold(s).");
})->purpose('Close reservations whose pickup period has passed');

Schedule::command('library:expire-holds')->dailyAt('00:05');
