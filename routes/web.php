<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'login'])->name('login');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::post('/generate', [HomeController::class, 'generate'])->name('generate');
Route::get('/login', [HomeController::class, 'login']);
Route::post('/login', [HomeController::class, 'authenticate'])->name('login.authenticate');
Route::post('/logout', [HomeController::class, 'logout'])->name('logout');
Route::get('/register', [HomeController::class, 'register'])->name('register');
Route::post('/register', [HomeController::class, 'storeRegistration'])->name('register.store');

Route::middleware('portal:student')->group(function (): void {
    Route::get('/student/dashboard', [HomeController::class, 'studentDashboard'])->name('student.dashboard');
    Route::get('/student/{section}', [HomeController::class, 'studentSection'])
        ->whereIn('section', ['borrow-return', 'reservations', 'history', 'fines', 'notifications', 'account', 'support', 'scan', 'circulation-policy', 'opac-guide'])
        ->name('student.section');
    Route::post('/fines/{fine}/pay', [HomeController::class, 'payFine'])->name('fines.pay');
});

Route::middleware('portal:faculty')->group(function (): void {
    Route::get('/faculty/dashboard', [HomeController::class, 'facultyDashboard'])->name('faculty.dashboard');
    Route::get('/staff/{section}', [HomeController::class, 'staffSection'])
        ->whereIn('section', ['circulation', 'patrons', 'profile', 'catalog', 'reservations', 'reports', 'history', 'academic', 'course-reserves', 'reading-lists', 'instruction-sessions', 'inter-library-loan', 'notifications', 'feedback', 'settings', 'support'])
        ->name('staff.section');
});

Route::middleware('portal:staff,superadmin')->group(function (): void {
    Route::get('/staff/dashboard', [HomeController::class, 'staffDashboard'])->name('staff.dashboard');
    Route::get('/librarian/dashboard', [HomeController::class, 'librarianDashboard'])->name('librarian.dashboard');
    Route::get('/librarian/{section}', [HomeController::class, 'librarianSection'])
        ->whereIn('section', [
            'books', 'add-book', 'categories', 'inventory',
            'borrow-requests', 'returns', 'overdue',
            'users', 'verification', 'memberships', 'reservations',
            'digital-resources', 'fines', 'reports', 'announcements',
            'feedback', 'staff-management', 'activity-logs', 'settings',
            'notifications', 'profile', 'support',
        ])
        ->name('librarian.section');
});

Route::middleware('portal:superadmin')->group(function (): void {
    Route::get('/super-admin/dashboard', [HomeController::class, 'superAdminDashboard'])->name('superadmin.dashboard');
    Route::get('/super-admin/{section}', [HomeController::class, 'superAdminSection'])
        ->whereIn('section', [
            'users', 'roles', 'approvals', 'librarians', 'library-staff', 'branches',
            'borrowing-rules', 'fine-settings', 'system-policies', 'reports',
            'database-backup', 'database-restore', 'data-export', 'security',
            'audit-logs', 'announcements', 'content', 'notifications',
            'maintenance', 'profile',
        ])
        ->name('superadmin.section');
});

Route::middleware('portal:student,faculty')->group(function (): void {
    Route::get('/books/catalog', [HomeController::class, 'booksCatalog'])->name('books.catalog');
    Route::post('/books/{book}/hold', [HomeController::class, 'holdBook'])->name('books.hold');
    Route::post('/reservations/{book}/cancel', [HomeController::class, 'cancelReservation'])->name('reservations.cancel');
    Route::post('/loans/{loan}/renew', [HomeController::class, 'renewLoan'])->name('loans.renew');
});
