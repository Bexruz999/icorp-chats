<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use App\Jobs\AmoSendMessage;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\Message\PrivateMessage;
use danog\MadelineProto\EventHandler\Update;
use danog\MadelineProto\SimpleEventHandler;

class TelegramIncomingMessage extends SimpleEventHandler
{

    #[Handler]
    public function handleMessage(Message $message): void
    {
        $fullInfo = $this->getFullInfo($message->senderId);
        TelegramMessage::dispatch($message);
        if (get_class($message) === PrivateMessage::class) AmoSendMessage::dispatch($message, $fullInfo['User']);
    }
}
