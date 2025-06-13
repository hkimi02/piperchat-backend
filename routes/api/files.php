<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FileController;

Route::middleware('auth:api')->group(function () {
    Route::post('/projects/{project}/files', [FileController::class, 'store']);
    Route::get('/files/{file}', [FileController::class, 'show']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);
});
