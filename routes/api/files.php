<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileController;

Route::middleware('auth:api')->group(function () {
        Route::post('/chatrooms/{chatroom}/files', [FileController::class, 'store'])->name('files.store');
    Route::get('/files/{file}', [FileController::class, 'show']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);
});
