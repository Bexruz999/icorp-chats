<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\SimpleEventHandler;
use danog\MadelineProto\EventHandler\Message\GroupMessage;
use Illuminate\Support\Carbon;

class TelegramIncomingMessage extends SimpleEventHandler
{

    private array $dialogs = [];



    #[Handler]

    public function handleMessage(Incoming&Message $message): void
    {

        TelegramMessage::dispatch([
            'id' => $message->chatId,
            'message' => $message->message ?? '',
            'user' => [
                'id' => $message->senderId,
                'self' => false
            ],
            'time'   => Carbon::parse($message->date)->timezone('+5')->format('H:i'),
            'type' => (get_class($message) === GroupMessage::class) ? 'chat' : 'user',
        ]);
    }
}
