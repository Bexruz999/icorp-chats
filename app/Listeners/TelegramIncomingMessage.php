<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\SendAmoCrmMessage;
use App\Events\TelegramMessage;
use App\Models\Shop;
use App\Services\AmoChatService;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\SimpleEventHandler;
use Str;

class TelegramIncomingMessage extends SimpleEventHandler
{

    #[Handler]
    public function handleMessage(Message $message): void
    {
        TelegramMessage::dispatch($message);

        SendAmoCrmMessage::dispatch($message);

       $data = [
           'chat_id' => 12345,
           'id'  => 176570,
           'message'     => 'ftftftkeffeug'
       ];

       $m = new Shop();
       $m->account_id = 1;
       $m->name = 'test';
       $m->slug = 'test';
       $m->save();

        (new AmoChatService())->sendMessage(
            peer_id: $data['chat_id'],
            msg_id: $data['id'],
            msg: $data['message']
        );

        $m = new Shop();
        $m->account_id = 1;
        $m->name = 'test2';
        $m->slug = 'test2';
        $m->save();
    }
}
