<?php

namespace App\Console\Commands;

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
    public function handle(AmoChatService $service)
    {
        $response = $service->connect();

        $response2 = $service->createChat();

        $response3 = $service->sendMEssage($response2);
        echo $response3;
    }
}
