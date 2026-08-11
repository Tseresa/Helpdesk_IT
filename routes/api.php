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
Route::post('/login', [AuthController::class, 'login']);

// ------------------------------------------------------------------
// Butuh autentikasi (Sanctum)
// ------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Data referensi - hanya admin yang boleh menulis (atur via middleware 'role:Admin' di controller/route jika perlu)
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('priorities', PriorityController::class);
    Route::apiResource('sla-policies', SlaPolicyController::class);

    // Pengguna
    Route::apiResource('users', UserController::class);
    Route::patch('users/{user}/password', [UserController::class, 'changePassword']);
    Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate']);
    Route::patch('users/{user}/activate', [UserController::class, 'activate']);

    // Tiket - modul inti
    Route::apiResource('tickets', TicketController::class);
    Route::patch('tickets/{ticket}/assign', [TicketController::class, 'assign']);
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'changeStatus']);
    Route::patch('tickets/{ticket}/escalate', [TicketController::class, 'escalate']);

    // Komentar & lampiran tiket (nested resource)
    Route::get('tickets/{ticket}/comments', [TicketCommentController::class, 'index']);
    Route::post('tickets/{ticket}/comments', [TicketCommentController::class, 'store']);
    Route::delete('comments/{comment}', [TicketCommentController::class, 'destroy']);

    Route::get('tickets/{ticket}/attachments', [AttachmentController::class, 'index']);
    Route::post('tickets/{ticket}/attachments', [AttachmentController::class, 'store']);
    Route::delete('attachments/{attachment}', [AttachmentController::class, 'destroy']);

    // Umpan balik tiket
    Route::post('tickets/{ticket}/feedback', [FeedbackController::class, 'store']);
    Route::get('tickets/{ticket}/feedback', [FeedbackController::class, 'show']);
    Route::get('feedback/summary', [FeedbackController::class, 'summary']);

    // Aset IT
    Route::apiResource('assets', AssetController::class);
    Route::post('tickets/{ticket}/assets', [AssetController::class, 'attachToTicket']);

    // Basis pengetahuan
    Route::apiResource('knowledge-articles', KnowledgeArticleController::class);

    // Notifikasi
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy']);

    // Dashboard & laporan
    Route::get('dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('dashboard/tickets-by-category', [DashboardController::class, 'ticketsByCategory']);
    Route::get('dashboard/tickets-by-agent', [DashboardController::class, 'ticketsByAgent']);
    Route::get('dashboard/sla-at-risk', [DashboardController::class, 'slaAtRisk']);
});
