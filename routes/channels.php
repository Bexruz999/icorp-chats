<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

//Broadcast::channel('telegram-messages', function (User $user) {
//    return true;
//});

//Broadcast::channel('dialogs', function (User $user) {
//    return true;
//});
Broadcast::channel('telegram-messages', \App\Broadcasting\TelegrammessageChannel::class);
