<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\SimpleEventHandler;
use danog\MadelineProto\EventHandler\Message\GroupMessage;

class TelegramIncomingMessage extends SimpleEventHandler
{

    private array $dialogs = [];



    #[Handler]

    public function handleMessage(Incoming&Message $message): void
    {

        //$message = mb_convert_encoding($message, 'UTF-8', 'auto');

        TelegramMessage::dispatch([
            'id' => $message->chatId,
            'message' => $message->message ?? '',
            'user' => [
                'id' => $message->senderId,
                'self' => false
            ],
            'time'    => date('H:i:s', $message->date ?? time()),
            'type' => (get_class($message) === GroupMessage::class) ? 'chat' : 'user',
        ]);
    }
}
