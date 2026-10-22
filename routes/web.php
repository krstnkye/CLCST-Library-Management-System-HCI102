<?php

use App\Http\Controllers\AccountManagementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkDataController;
use App\Http\Controllers\LibrarianController;
use App\Http\Controllers\PatronController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'create'])->name('login');
    Route::get('/login', [AuthController::class, 'create']);
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.authenticate');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegistration'])->middleware('throttle:5,10')->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

// Every signed-in, active user can update their own contact details and password.
Route::middleware(['auth', 'portal'])->prefix('profile')->name('profile.')->group(function (): void {
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::put('/password', [ProfileController::class, 'password'])->middleware('throttle:6,1')->name('password');
});

Route::middleware(['auth', 'portal:student'])->group(function (): void {
    Route::get('/student/dashboard', [PatronController::class, 'studentDashboard'])->name('student.dashboard');
    Route::get('/student/{section}', [PatronController::class, 'studentSection'])
        ->whereIn('section', array_keys(PatronController::STUDENT_SECTIONS))
        ->name('student.section');
});

Route::middleware(['auth', 'portal:faculty'])->group(function (): void {
    Route::get('/faculty/dashboard', [PatronController::class, 'facultyDashboard'])->name('faculty.dashboard');
    Route::get('/faculty/{section}', [PatronController::class, 'facultySection'])
        ->whereIn('section', array_keys(PatronController::FACULTY_SECTIONS))
        ->name('staff.section');
});

Route::middleware(['auth', 'portal:student,faculty'])->group(function (): void {
    Route::get('/books/catalog', [PatronController::class, 'catalog'])->name('books.catalog');
    Route::post('/books/{book}/hold', [PatronController::class, 'reserve'])->middleware('throttle:20,1')->name('books.hold');
    Route::post('/reservations/{reservation}/cancel', [PatronController::class, 'cancelReservation'])->name('reservations.cancel');
    Route::post('/loans/{loan}/renew', [PatronController::class, 'renew'])->name('loans.renew');
    Route::post('/requests', [PatronController::class, 'storeRequest'])->middleware('throttle:10,1')->name('patron.requests.store');
});

Route::middleware(['auth', 'portal:librarian,admin,superadmin'])->prefix('librarian')->name('librarian.')->group(function (): void {
    Route::get('/dashboard', [LibrarianController::class, 'dashboard'])->name('dashboard');
    Route::get('/{section}', [LibrarianController::class, 'section'])
        ->whereIn('section', [...array_keys(LibrarianController::PAGES), 'users', 'verification'])
        ->name('section');

    Route::post('/books', [LibrarianController::class, 'storeBook'])->name('books.store');
    Route::get('/books/{book}/edit', [LibrarianController::class, 'editBook'])->name('books.edit');
    Route::put('/books/{book}', [LibrarianController::class, 'updateBook'])->name('books.update');
    Route::post('/books/{book}/copies', [LibrarianController::class, 'storeCopies'])->name('books.copies.store');
    Route::patch('/copies/{copy}', [LibrarianController::class, 'updateCopy'])->name('copies.update');

    Route::post('/desk/checkout', [LibrarianController::class, 'checkout'])->name('checkout');
    Route::post('/desk/checkin', [LibrarianController::class, 'checkin'])->name('checkin');
    Route::post('/loans/{loan}/checkin', [LibrarianController::class, 'checkinLoan'])->name('loans.checkin');
    Route::post('/reservations/{reservation}/ready', [LibrarianController::class, 'readyReservation'])->name('reservations.ready');
    Route::post('/reservations/{reservation}/issue', [LibrarianController::class, 'issueReservation'])->name('reservations.issue');
    Route::post('/reservations/{reservation}/cancel', [LibrarianController::class, 'cancelReservation'])->name('reservations.cancel');
    Route::post('/fines/{fine}/settle', [LibrarianController::class, 'settleFine'])->name('fines.settle');
    Route::patch('/requests/{libraryRequest}', [LibrarianController::class, 'updateRequest'])->name('requests.update');
    Route::post('/announcements', [LibrarianController::class, 'storeAnnouncement'])->name('announcements.store');
    Route::delete('/announcements/{announcement}', [LibrarianController::class, 'destroyAnnouncement'])->name('announcements.destroy');
});

Route::middleware(['auth', 'portal:admin,superadmin'])->group(function (): void {
    Route::get('/admin/dashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/admin/{section}', [AdminController::class, 'adminSection'])
        ->whereIn('section', [...array_keys(AdminController::ADMIN_PAGES), 'bulk-data', 'announcements'])
        ->name('admin.section');
});

Route::middleware(['auth', 'portal:superadmin'])->group(function (): void {
    Route::get('/super-admin/dashboard', [AdminController::class, 'superAdminDashboard'])->name('superadmin.dashboard');
    Route::get('/super-admin/{section}', [AdminController::class, 'superAdminSection'])
        ->whereIn('section', [...array_keys(AdminController::SUPER_PAGES), 'users', 'approvals', 'librarians', 'library-staff', 'data-export', 'announcements', 'circulation'])
        ->name('superadmin.section');
    Route::put('/super-admin/settings', [AdminController::class, 'updateSettings'])->name('superadmin.settings.update');
});

Route::middleware(['auth', 'portal:librarian,admin,superadmin'])->prefix('management/data')->name('management.data.')->group(function (): void {
    Route::get('/', [BulkDataController::class, 'index'])->name('index');
    Route::post('/import/{type}', [BulkDataController::class, 'import'])->middleware('throttle:10,1')->name('import');
    Route::get('/export/{type}', [BulkDataController::class, 'export'])->middleware('throttle:20,1')->name('export');
    Route::get('/template/{type}', [BulkDataController::class, 'template'])->name('template');
});

Route::middleware(['auth', 'portal:librarian,admin,superadmin'])->prefix('management/accounts')->name('management.accounts.')->group(function (): void {
    Route::get('/', [AccountManagementController::class, 'index'])->name('index');
    Route::get('/{user}/credential', [AccountManagementController::class, 'credential'])->name('credential');
    Route::post('/{user}/approve', [AccountManagementController::class, 'approve'])->name('approve');
    Route::post('/{user}/suspend', [AccountManagementController::class, 'suspend'])->name('suspend');
});
