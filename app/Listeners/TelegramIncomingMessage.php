<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\Plugin\RestartPlugin;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\SimpleEventHandler;

class TelegramIncomingMessage extends SimpleEventHandler
{

    #[Handler]
    public function handleMessage(Incoming&Message $message): void
    {
        TelegramMessage::dispatch($message);
    }
}
