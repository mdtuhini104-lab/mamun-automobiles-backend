<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('pos', function ($user) {
    return $user !== null; // Allow all authenticated users (can be restricted by role later)
});

Broadcast::channel('stock', function ($user) {
    return $user !== null; 
});

Broadcast::channel('dashboard', function ($user) {
    return $user !== null; 
});

Broadcast::channel('jobcard.{id}', function ($user, $id) {
    return $user !== null; 
});

Broadcast::channel('notifications.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('live-sync', function ($user) {
    return ['id' => $user->id, 'name' => $user->name]; // Presence channel for multi-user sync
});
