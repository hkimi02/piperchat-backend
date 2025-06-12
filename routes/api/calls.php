<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CallController;

Route::middleware('auth:api')->group(function () {
    Route::post('/calls/signal/{chatroom}', [CallController::class, 'signal']);
});
