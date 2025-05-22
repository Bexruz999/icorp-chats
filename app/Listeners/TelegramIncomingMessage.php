<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use App\Jobs\AmoIncomingMessage;
use App\Models\User;
use Arr;
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
        $fullInfo = $this->getFullInfo($message->senderId);
        TelegramMessage::dispatch($message);

        $a = get_class($message) === PrivateMessage::class;

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

            $a = json_decode(json_encode($message), true);

            AmoIncomingMessage::dispatch($a, $fullInfo['User'], $amojoId);
        }
    }

    public function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = $this->utf8ize($v);
            }
        } elseif (is_string($d)) {
            return mb_convert_encoding($d, 'UTF-8', 'auto');
        }
        return $d;
    }
}
