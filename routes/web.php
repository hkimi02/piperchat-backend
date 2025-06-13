<?php

use App\Http\Controllers\FileAccessController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/files/{file}', [FileAccessController::class, 'show'])
    ->middleware('signed')
    ->name('files.show');
