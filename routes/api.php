<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;


require __DIR__ . '/api/authRoutes.php';
require __DIR__ . '/api/organisation.php';
require __DIR__ . '/api/messages.php';
require __DIR__ . '/api/chatrooms.php';
require __DIR__ . '/api/project.php';
require __DIR__ . '/api/tasks.php';
require __DIR__ . '/api/calls.php';
require __DIR__ . '/api/files.php';

Route::middleware('auth:api')->group(function () {
    Broadcast::routes();
});
