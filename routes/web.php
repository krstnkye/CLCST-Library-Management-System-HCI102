<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountManagementController;
use App\Http\Controllers\BulkDataController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'create'])->name('login');
    Route::get('/login', [AuthController::class, 'create']);
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.authenticate');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegistration'])->middleware('throttle:5,10')->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'portal:student'])->group(function (): void {
    Route::get('/student/dashboard', [HomeController::class, 'studentDashboard'])->name('student.dashboard');
    Route::get('/student/{section}', [HomeController::class, 'studentSection'])
        ->whereIn('section', ['borrow-return', 'reservations', 'history', 'fines', 'notifications', 'account', 'support', 'scan', 'circulation-policy', 'opac-guide'])
        ->name('student.section');
    Route::post('/fines/{fine}/pay', [HomeController::class, 'payFine'])->name('fines.pay');
});

Route::middleware(['auth', 'portal:faculty'])->group(function (): void {
    Route::get('/faculty/dashboard', [HomeController::class, 'facultyDashboard'])->name('faculty.dashboard');
    Route::get('/faculty/{section}', [HomeController::class, 'staffSection'])
        ->whereIn('section', ['circulation', 'patrons', 'profile', 'catalog', 'reservations', 'reports', 'history', 'academic', 'course-reserves', 'reading-lists', 'instruction-sessions', 'inter-library-loan', 'notifications', 'feedback', 'settings', 'support'])
        ->name('staff.section');
});

Route::middleware(['auth', 'portal:librarian,admin,superadmin'])->group(function (): void {
    Route::get('/librarian/dashboard', [HomeController::class, 'librarianDashboard'])->name('librarian.dashboard');
    Route::get('/librarian/{section}', [HomeController::class, 'librarianSection'])
        ->whereIn('section', [
            'books', 'add-book', 'categories', 'inventory', 'borrow-requests', 'returns',
            'overdue', 'users', 'verification', 'memberships', 'reservations',
            'digital-resources', 'fines', 'reports', 'announcements', 'feedback',
            'staff-management', 'activity-logs', 'settings', 'notifications',
            'profile', 'support',
        ])->name('librarian.section');
});

Route::middleware(['auth', 'portal:admin,superadmin'])->group(function (): void {
    Route::get('/admin/dashboard', [HomeController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/{section}', [HomeController::class, 'adminSection'])
        ->whereIn('section', ['users', 'approvals', 'books', 'circulation', 'reports', 'bulk-data', 'audit-logs', 'announcements', 'profile'])
        ->name('admin.section');
});

Route::middleware(['auth', 'portal:superadmin'])->group(function (): void {
    Route::get('/super-admin/dashboard', [HomeController::class, 'superAdminDashboard'])->name('superadmin.dashboard');
    Route::get('/super-admin/{section}', [HomeController::class, 'superAdminSection'])
        ->whereIn('section', [
            'users', 'roles', 'approvals', 'librarians', 'library-staff', 'branches',
            'borrowing-rules', 'fine-settings', 'system-policies', 'reports',
            'database-backup', 'database-restore', 'data-export', 'security',
            'audit-logs', 'announcements', 'content', 'notifications',
            'maintenance', 'profile',
        ])->name('superadmin.section');
});

Route::middleware(['auth', 'portal:student,faculty'])->group(function (): void {
    Route::get('/books/catalog', [HomeController::class, 'booksCatalog'])->name('books.catalog');
    Route::post('/books/{book}/hold', [HomeController::class, 'holdBook'])->name('books.hold');
    Route::post('/reservations/{book}/cancel', [HomeController::class, 'cancelReservation'])->name('reservations.cancel');
    Route::post('/loans/{loan}/renew', [HomeController::class, 'renewLoan'])->name('loans.renew');
});

Route::middleware(['auth', 'portal:librarian,admin,superadmin'])->prefix('management/data')->name('management.data.')->group(function (): void {
    Route::get('/', [BulkDataController::class, 'index'])->name('index');
    Route::post('/import/{type}', [BulkDataController::class, 'import'])->middleware('throttle:10,1')->name('import');
    Route::get('/export/{type}', [BulkDataController::class, 'export'])->middleware('throttle:20,1')->name('export');
    Route::get('/template/{type}', [BulkDataController::class, 'template'])->name('template');
});

Route::middleware(['auth', 'portal:librarian,admin,superadmin'])->prefix('management/accounts')->name('management.accounts.')->group(function (): void {
    Route::get('/', [AccountManagementController::class, 'index'])->name('index');
    Route::post('/{user}/approve', [AccountManagementController::class, 'approve'])->middleware('throttle:30,1')->name('approve');
    Route::post('/{user}/suspend', [AccountManagementController::class, 'suspend'])->middleware('throttle:30,1')->name('suspend');
});
