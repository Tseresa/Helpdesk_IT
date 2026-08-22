<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AttachmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\KnowledgeArticleController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PriorityController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SlaPolicyController;
use App\Http\Controllers\Api\TicketCommentController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// ------------------------------------------------------------------
// Publik (tanpa autentikasi)
// ------------------------------------------------------------------
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

// ------------------------------------------------------------------
// Butuh autentikasi (Sanctum)
// PENTING: seluruh route di bawah diberi prefix nama 'api.' (lihat
// Route::name('api.') di baris pembuka group) supaya nama route TIDAK
// bentrok dengan nama route yang sama di routes/web.php (mis. 'tickets.store').
// Tanpa prefix ini, route() helper bisa salah resolve ke URL /api/... saat
// dipanggil dari Blade view, menyebabkan submit form kena "Unauthenticated."
// karena diarahkan ke endpoint Sanctum yang butuh Bearer token, bukan session.
// ------------------------------------------------------------------
Route::name('api.')->middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/me', [AuthController::class, 'me'])->name('me');

    // Data referensi - hanya admin yang boleh menulis (atur via middleware 'role:Admin' di controller/route jika perlu)
    Route::apiResource('roles', RoleController::class)->names('roles');
    Route::apiResource('departments', DepartmentController::class)->names('departments');
    Route::apiResource('categories', CategoryController::class)->names('categories');
    Route::apiResource('priorities', PriorityController::class)->names('priorities');
    Route::apiResource('sla-policies', SlaPolicyController::class)->names('sla-policies');

    // Pengguna
    Route::apiResource('users', UserController::class)->names('users');
    Route::patch('users/{user}/password', [UserController::class, 'changePassword'])->name('users.password');
    Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::patch('users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');

    // Tiket - modul inti
    Route::apiResource('tickets', TicketController::class)->names('tickets');
    Route::patch('tickets/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'changeStatus'])->name('tickets.changeStatus');
    Route::patch('tickets/{ticket}/escalate', [TicketController::class, 'escalate'])->name('tickets.escalate');

    // Komentar & lampiran tiket (nested resource)
    Route::get('tickets/{ticket}/comments', [TicketCommentController::class, 'index'])->name('tickets.comments.index');
    Route::post('tickets/{ticket}/comments', [TicketCommentController::class, 'store'])->name('tickets.comments.store');
    Route::delete('comments/{comment}', [TicketCommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('tickets/{ticket}/attachments', [AttachmentController::class, 'index'])->name('tickets.attachments.index');
    Route::post('tickets/{ticket}/attachments', [AttachmentController::class, 'store'])->name('tickets.attachments.store');
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    // Umpan balik tiket
    Route::post('tickets/{ticket}/feedback', [FeedbackController::class, 'store'])->name('tickets.feedback.store');
    Route::get('tickets/{ticket}/feedback', [FeedbackController::class, 'show'])->name('tickets.feedback.show');
    Route::get('feedback/summary', [FeedbackController::class, 'summary'])->name('feedback.summary');

    // Aset IT
    Route::apiResource('assets', AssetController::class)->names('assets');
    Route::post('tickets/{ticket}/assets', [AssetController::class, 'attachToTicket'])->name('tickets.assets.attach');

    // Basis pengetahuan
    Route::apiResource('knowledge-articles', KnowledgeArticleController::class)->names('knowledge-articles');

    // Notifikasi
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Dashboard & laporan
    Route::get('dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
    Route::get('dashboard/tickets-by-category', [DashboardController::class, 'ticketsByCategory'])->name('dashboard.ticketsByCategory');
    Route::get('dashboard/tickets-by-agent', [DashboardController::class, 'ticketsByAgent'])->name('dashboard.ticketsByAgent');
    Route::get('dashboard/sla-at-risk', [DashboardController::class, 'slaAtRisk'])->name('dashboard.slaAtRisk');
});