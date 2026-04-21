<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdoptionApplicationController;
use App\Http\Controllers\AnimalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\PublicStatsController;
use App\Http\Controllers\SupportChatController;
use App\Http\Controllers\SupportMessageController;
use App\Http\Controllers\UserNotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/stats', [PublicStatsController::class, 'index']);
Route::get('/animals', [AnimalController::class, 'index']);
Route::get('/animals/{id}', [AnimalController::class, 'show'])->whereNumber('id');

Route::post('/contact-messages', [ContactMessageController::class, 'store']);

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:12,1');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');
Route::post('/admin/login', [AuthController::class, 'adminPanelLogin'])
    ->middleware('throttle:8,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::post('/support-messages', [SupportMessageController::class, 'store']);

    Route::get('/my/support-chat/unread-count', [SupportChatController::class, 'myUnreadCount']);
    Route::get('/my/support-chat', [SupportChatController::class, 'myIndex']);
    Route::post('/my/support-chat', [SupportChatController::class, 'myStore']);

    Route::get('/my/applications', [AdoptionApplicationController::class, 'mine']);
    Route::post('/applications', [AdoptionApplicationController::class, 'store']);
    Route::delete('/my/applications/{id}', [AdoptionApplicationController::class, 'destroyMine'])->whereNumber('id');

    Route::get('/my/notifications', [UserNotificationController::class, 'index']);
    Route::post('/my/notifications/mark-read', [UserNotificationController::class, 'markRead']);
    Route::delete('/my/notifications/{id}', [UserNotificationController::class, 'destroy'])->whereNumber('id');
    Route::post('/decline-admin-role', [UserNotificationController::class, 'declineAdminRole']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/activity-logs', [ActivityLogController::class, 'index']);

    Route::get('/contact-messages', [ContactMessageController::class, 'adminIndex']);
    Route::patch('/contact-messages/{id}', [ContactMessageController::class, 'adminUpdate'])->whereNumber('id');
    Route::delete('/contact-messages/{id}', [ContactMessageController::class, 'adminDestroy'])->whereNumber('id');
    Route::post('/cancel-admin-role-offer', [ContactMessageController::class, 'adminCancelRoleGrant']);

    Route::get('/applications', [AdoptionApplicationController::class, 'adminIndex']);
    Route::post('/applications/{id}/approve', [AdoptionApplicationController::class, 'adminApprove'])->whereNumber('id');
    Route::delete('/applications/{id}', [AdoptionApplicationController::class, 'adminDestroy'])->whereNumber('id');

    Route::post('/animals', [AnimalController::class, 'store']);
    Route::patch('/animals/{id}', [AnimalController::class, 'update'])->whereNumber('id');
    Route::delete('/animals/{id}', [AnimalController::class, 'destroy'])->whereNumber('id');

    Route::get('/users', [AdminUserController::class, 'index']);
    Route::patch('/users/{id}', [AdminUserController::class, 'update'])->whereNumber('id');

    Route::get('/support-chat/threads', [SupportChatController::class, 'adminThreads']);
    Route::get('/support-chat/users/{user}', [SupportChatController::class, 'adminShow'])->whereNumber('user');
    Route::post('/support-chat/users/{user}', [SupportChatController::class, 'adminStore'])->whereNumber('user');
});
