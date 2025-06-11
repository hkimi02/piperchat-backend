<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('organisation.{id}', function (User $user, int $id) {
    Log::info('Authorizing broadcast channel.', [
        'user_id' => $user->id,
        'user_organisation_id' => $user->organisation_id,
        'channel_organisation_id' => $id,
        'match' => ($user->organisation_id === $id)
    ]);

    if ($user->organisation_id === $id) {
        return ['id' => $user->id, 'full_name' => $user->full_name];
    }
    return false;
});
