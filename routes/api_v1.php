<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\SpaceController;
use App\Http\Controllers\Api\V1\NotificationController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Space - Groups/Squads
    Route::get('/space/groups', [SpaceController::class, 'groups']);
    Route::get('/space/groups/{group}', [SpaceController::class, 'showGroup']);
    Route::post('/space/groups/{group}/threads', [SpaceController::class, 'storeGroupThread']);

    // Space - Wall Publik
    Route::get('/space/threads', [SpaceController::class, 'threads']);
    Route::post('/space/threads', [SpaceController::class, 'storeThread']);
    Route::get('/space/threads/{thread}', [SpaceController::class, 'showThread']);
    Route::put('/space/threads/{thread}', [SpaceController::class, 'updateThread']);
    Route::delete('/space/threads/{thread}', [SpaceController::class, 'destroyThread']);

    // Space - Interactions
    Route::post('/space/threads/{thread}/replies', [SpaceController::class, 'storeReply']);
    Route::delete('/space/replies/{reply}', [SpaceController::class, 'destroyReply']);
    Route::post('/space/threads/{thread}/like', [SpaceController::class, 'toggleLike']);
    Route::post('/space/threads/{thread}/reactions', [SpaceController::class, 'toggleReaction']);
    Route::post('/space/replies/{reply}/reactions', [SpaceController::class, 'toggleReplyReaction']);
    Route::post('/space/polls/{poll}/vote', [SpaceController::class, 'votePoll']);

    // Notifications
    Route::post('/notifications/device-token', [NotificationController::class, 'registerToken']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
});
