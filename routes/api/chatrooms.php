<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatroomController;

Route::middleware('auth:api')->group(function () {
    Route::get('/chatrooms', [ChatroomController::class, 'index']);
});
