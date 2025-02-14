<?php

namespace App\Broadcasting;

use App\Models\User;

class TelegrammessageChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     */
    public function join(User $user): array|bool{
        return auth()->check() && $user->account->id === auth()->user()->account->id;
    }
}
