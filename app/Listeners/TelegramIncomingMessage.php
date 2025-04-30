<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\SendAmoCrmMessage;
use App\Events\TelegramMessage;
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
    }
}
