<?php

use Illuminate\Support\Facades\Broadcast;

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

// Canal privé par utilisateur (notifications personnelles)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal public pour les alertes de présence (admin + superviseurs)
Broadcast::channel('presence-alerts', function ($user) {
    return in_array($user->role, ['Administrateur', 'Superviseur']);
});
