<?php

use App\Http\Controllers\Organisation\OrganisationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('organisation')->group(function () {
    Route::get('users', [OrganisationController::class, 'getUsers']);
    Route::post('invite', [OrganisationController::class, 'inviteMember']);
    Route::post('generate-code', [OrganisationController::class, 'generateInviteCode']);
    Route::delete('users/{userToRemove}', [OrganisationController::class, 'removeUser']);
});
