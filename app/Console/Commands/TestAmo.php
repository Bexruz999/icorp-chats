<?php

namespace App\Console\Commands;

use App\Events\SendAmoCrmMessage;
use App\Services\AmoChatService;
use Illuminate\Console\Command;

class TestAmo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-amo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(AmoChatService $amoChatService)
    {
        SendAmoCrmMessage::dispatch([
            'peer_id' => 123,
            'msg_id'  => 123,
            'msg'     => 'test'
        ]);
    }
}
