<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatroomController;

Route::middleware('auth:api')->group(function () {
    Route::get('/chatrooms', [ChatroomController::class, 'index']);
    Route::post('/chatrooms', [ChatroomController::class, 'store']);
    Route::post('/chatrooms/private/find-or-create', [ChatroomController::class, 'findOrCreatePrivateChatroom']);
});
