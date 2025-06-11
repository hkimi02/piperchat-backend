<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;


require __DIR__ . '/api/authRoutes.php';
require __DIR__ . '/api/organisation.php';
require __DIR__ . '/api/messages.php';
require __DIR__ . '/api/chatrooms.php';

Route::middleware('auth:api')->group(function () {
    Broadcast::routes();
});
