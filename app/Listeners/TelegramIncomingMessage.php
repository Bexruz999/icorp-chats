<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use App\Jobs\AmoIncomingMessage;
use App\Models\User;
use App\Services\TelegramService;
use Cache;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\Message\PrivateMessage;
use danog\MadelineProto\SimpleEventHandler;

class TelegramIncomingMessage extends SimpleEventHandler
{

    #[Handler]
    public function handleMessage(Message $message): void
    {
        TelegramMessage::dispatch($message);

        if (get_class($message) === PrivateMessage::class) {

            $amojoId = null;
            if ($message->out) {
                $tgId = $this->getId($message->senderId);

                $user = Cache::remember('telegram_user_' . $tgId, 3600, function () use ($tgId) {
                    return User::where('telegram_id', $tgId)->first();
                });

                $amojoId = $user->amojo_id;
                var_dump('amojo: ' . $amojoId);
            }

            $msg =  json_decode(json_encode($message), true);
            $fullInfo = $this->getFullInfo($message->senderId);
            if ($message->media) {
                $msg['mediaType'] = TelegramService::getTelegramMediaType($message->media);
                $msg['self_phone'] = $this->getSelf()['phone'];
            }
            AmoIncomingMessage::dispatch($msg, $fullInfo['User'], $amojoId);
        }
    }
}
