<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;

Route::middleware('auth:api')->group(function () {
    Route::get('/chatrooms/{chatroom}/messages', [MessageController::class, 'index']);
    Route::post('/chatrooms/{chatroom}/messages', [MessageController::class, 'store']);
});
