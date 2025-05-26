<?php

namespace App\Console\Commands;

use App\Events\SendAmoCrmMessage;
use App\Models\User;
use App\Services\AmoChatService;
use Cache;
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

        $tgId = 781366976;
        $user = Cache::remember('telegram_user_' . $tgId, 3600, function () use ($tgId) {
            return User::where('telegram_id', $tgId)->first();
        });

        $amojoId = $user->amojo_id;

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
