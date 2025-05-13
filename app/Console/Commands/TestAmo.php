<?php

namespace App\Console\Commands;

use App\Events\SendAmoCrmMessage;
use App\Services\AmoChatService;
use Illuminate\Console\Command;
use Ufee\Amo\Oauthapi;

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

        $n = 7;

        for ($i = 2; $i < $n; $i++) {
            if ($n % $i == 0) {
                echo '+';
                break;
            }
        }

        /*$amo = Oauthapi::setInstance([
            'domain' => config('amo.domain'),
            'client_id' => config('amo.account_id'),
            'client_secret' => config('amo.secret_key'),
            'redirect_uri' => config('amo.redirect_uri'),
        ]);

        $leads = $amo->leads();
        $account = $amo->account();

        var_dump($amo, $leads, $account);*/

        /*$a = $amoChatService->getUsers(true);
        var_dump($a);*/
        /*SendAmoCrmMessage::dispatch([
            'chat_id' => 12345,
            'id'  => 176569,
            'message'     => 'ftftftkeffeug'
        ]);*/

        /*SendAmoCrmMessage::dispatch([
            'chat_id' => 12345,
            'id'  => 176569,
            'message'     => 'ftftftkeffeug'
        ]);*/
    }
}
