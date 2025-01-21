<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('telegram-messages', function (User $user) {
    return true;
});
