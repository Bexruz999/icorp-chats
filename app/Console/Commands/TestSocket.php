<?php

namespace App\Console\Commands;

use App\Events\TelegramMessage;
use App\Listeners\TelegramIncomingMessage;
use App\Services\TelegramService;
use Illuminate\Console\Command;

class TestSocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-socket';

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
        // TelegramMessage::dispatch("test mesa");
        $storage = storage_path() . '/app/telegram/+6282211915445.madeline';
        TelegramIncomingMessage::startAndLoop($storage);
    }
}
