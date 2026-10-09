<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'login'])->name('login');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::post('/generate', [HomeController::class, 'generate'])->name('generate');
Route::get('/login', [HomeController::class, 'login']);
Route::post('/login', [HomeController::class, 'authenticate'])->name('login.authenticate');
Route::get('/register', [HomeController::class, 'register'])->name('register');
Route::post('/register', [HomeController::class, 'storeRegistration'])->name('register.store');
Route::get('/student/dashboard', [HomeController::class, 'studentDashboard'])->name('student.dashboard');
Route::get('/faculty/dashboard', [HomeController::class, 'facultyDashboard'])->name('faculty.dashboard');
Route::get('/staff/dashboard', [HomeController::class, 'staffDashboard'])->name('staff.dashboard');
Route::get('/books/catalog', [HomeController::class, 'booksCatalog'])->name('books.catalog');
Route::post('/books/{book}/hold', [HomeController::class, 'holdBook'])->name('books.hold');
Route::post('/reservations/{book}/cancel', [HomeController::class, 'cancelReservation'])->name('reservations.cancel');
Route::post('/fines/{fine}/pay', [HomeController::class, 'payFine'])->name('fines.pay');
Route::post('/loans/{loan}/renew', [HomeController::class, 'renewLoan'])->name('loans.renew');
Route::get('/student/{section}', [HomeController::class, 'studentSection'])
    ->whereIn('section', ['borrow-return', 'reservations', 'history', 'fines', 'notifications', 'account', 'support', 'scan', 'circulation-policy', 'opac-guide'])
    ->name('student.section');
Route::get('/staff/{section}', [HomeController::class, 'staffSection'])
    ->whereIn('section', ['circulation', 'patrons', 'catalog', 'reservations', 'reports', 'history', 'academic', 'course-reserves', 'reading-lists', 'instruction-sessions', 'notifications', 'settings', 'support'])
    ->name('staff.section');
