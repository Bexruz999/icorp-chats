<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\SendAmoCrmMessage;
use App\Events\TelegramMessage;
use App\Services\AmoChatService;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Media\Audio;
use danog\MadelineProto\EventHandler\Media\Document;
use danog\MadelineProto\EventHandler\Media\Photo;
use danog\MadelineProto\EventHandler\Media\Video;
use danog\MadelineProto\EventHandler\Media\Voice;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\SimpleEventHandler;
use Str;

class TelegramIncomingMessage extends SimpleEventHandler
{

    #[Handler]
    public function handleMessage(Message $message): void
    {
        (new AmoChatService())->sendMessage(
            peer_id: rand(100, 1000),
            msg_id: rand(100, 1000),
            msg: Str::random(20),
        );
        TelegramMessage::dispatch($message);
    }
}
