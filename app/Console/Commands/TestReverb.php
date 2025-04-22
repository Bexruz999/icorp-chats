<?php

namespace App\Console\Commands;

use App\Events\TelegramMessage;
use App\Events\TelegramMessageShipped;
use App\Listeners\TelegramIncomingMessage;
use App\Models\User;
use App\Services\TelegramService;
use danog\MadelineProto\API;
use Illuminate\Console\Command;

class TestReverb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        echo 'test';
        TelegramMessage::dispatch(['test']);
    }
}
