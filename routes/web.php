<?php

use App\Http\Controllers\FileAccessController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/storage/projects/{projectId}/files/{filename}', [FileAccessController::class, 'getProjectFile'])
    ->middleware('signed')
    ->name('files.show');
