<?php

namespace App\Listeners;

use App\Events\TelegramMessage;
use App\Events\TelegramMessageShipped;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTelegramMessage
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TelegramMessageShipped $event): void
    {
        //TelegramMessageShipped::dispatch(['wfbwwrr']);
    }
}
