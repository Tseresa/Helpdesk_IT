<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\TicketController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

// ------------------------------------------------------------------
// Halaman depan
// ------------------------------------------------------------------
Route::get('/', function () {
    return redirect()->route('login');
});

// ------------------------------------------------------------------
// Tamu (belum login) - guest middleware mencegah user yang sudah
// login mengakses ulang halaman login/register
// ------------------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ------------------------------------------------------------------
// Sudah login (session) - semua role
// ------------------------------------------------------------------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');

    // Otorisasi per-role untuk aksi ini dicek di dalam controller
    // (canHandleTickets/canAssignTickets), bukan cuma disembunyikan di Blade.
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.updateStatus');
    Route::patch('/tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'addComment'])->name('tickets.addComment');

    // ------------------------------------------------------------------
    // Khusus Admin - Kelola Pengguna (FR-14)
    // Middleware 'role' didaftarkan di bootstrap/app.php, lihat README.
    // ------------------------------------------------------------------
    Route::middleware('role:Admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggleActive');
    });
});
