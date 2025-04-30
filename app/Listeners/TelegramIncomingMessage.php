<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\SendAmoCrmMessage;
use App\Events\TelegramMessage;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Media\Audio;
use danog\MadelineProto\EventHandler\Media\Document;
use danog\MadelineProto\EventHandler\Media\Photo;
use danog\MadelineProto\EventHandler\Media\Video;
use danog\MadelineProto\EventHandler\Media\Voice;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\SimpleEventHandler;

class TelegramIncomingMessage extends SimpleEventHandler
{

    #[Handler]
    public function handleMessage(Message $message): void
    {
        TelegramMessage::dispatch($message);
    }

    private function formatMedia($media): array
    {
        if (!$media) return [];

        return [
            '_' => $this->getTelegramMediaType($media),
            'document' => [
                'mime_type' => $media->mimeType ?? null,
                'file_name' => $media->fileName ?? null,
                'size' => $media->size ?? null,
            ],
            'caption' => $media->caption ?? null,
        ];
    }

    private function getTelegramMediaType($media): string
    {
        return match (true) {
            $media instanceof Document  => 'messageMediaDocument',
            $media instanceof Photo     => 'messageMediaPhoto',
            $media instanceof Video     => 'messageMediaVideo',
            $media instanceof Audio     => 'messageMediaAudio',
            $media instanceof Voice     => 'messageMediaVoice',
            default                     => 'messageMediaUnsupported',
        };
    }
}
