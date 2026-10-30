<?php

namespace App\Providers;

use App\Services\LibrarySettings;
use App\Support\PatronNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // One settings instance per request, so overrides are read once.
        $this->app->scoped(LibrarySettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Shared password policy for registration and password changes.
        Password::defaults(fn () => Password::min(10)->mixedCase()->letters()->numbers());

        // Badge counts shown in the student and faculty navigation.
        View::composer(['layouts.student', 'layouts.faculty', 'partials.student-sidebar', 'partials.staff-sidebar', 'books.catalog'], function ($view): void {
            $user = Auth::user();
            if (! $user) {
                return;
            }

            // Several views of one page need the same numbers; work them out once per request.
            $attributes = request()->attributes;
            if (! $attributes->has('patronSummary')) {
                $attributes->set('patronSummary', [
                    'activeLoans' => $user->loans()->active()->count(),
                    'overdueLoans' => $user->loans()->overdue()->count(),
                    'openReservations' => $user->reservations()->open()->count(),
                    'outstandingFines' => $user->outstandingFineTotal(),
                    'notifications' => PatronNotifications::actionableCount($user),
                ]);
            }
            $view->with('patronSummary', $attributes->get('patronSummary'));
        });
    }
}
