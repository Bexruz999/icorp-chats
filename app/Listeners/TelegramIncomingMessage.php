<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Media\Audio;
use danog\MadelineProto\EventHandler\Media\Document;
use danog\MadelineProto\EventHandler\Media\Photo;
use danog\MadelineProto\EventHandler\Media\Video;
use danog\MadelineProto\EventHandler\Media\Voice;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\SimpleEventHandler;
use danog\MadelineProto\EventHandler\Message\GroupMessage;
use Illuminate\Support\Carbon;

class TelegramIncomingMessage extends SimpleEventHandler
{

    #[Handler]
    public function handleMessage(Message $message): void
    {

        $result = [
            'id' => $message->id,
            'chat_id' => $message->chatId,
            'message' => $message->message ?? '',
            'user' => [
                'id' => $message->senderId,
                'self' => $message->out
            ],
            'time'   => Carbon::parse($message->date)->timezone('+5')->format('H:i'),
            'type' => (get_class($message) === GroupMessage::class) ? 'chat' : 'user'
        ];

        if ($message->media) {
            $result['media'] = $this->formatMedia($message->media);
        }

        TelegramMessage::dispatch($result);
    }

    /*private function getTelegramMediaType($media): string|false
    {
        if ($media === null) {
            return false;
        }

        $mediaClass = get_class($media);

        return match (true) {
            str_contains($mediaClass, 'Document') => 'messageMediaDocument',
            str_contains($mediaClass, 'Photo') => 'messageMediaPhoto',
            str_contains($mediaClass, 'Video') => 'messageMediaVideo',
            str_contains($mediaClass, 'Audio') => 'messageMediaAudio',
            default => false,
        };
    }*/

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
            $media instanceof Document => 'messageMediaDocument',
            $media instanceof Photo => 'messageMediaPhoto',
            $media instanceof Video => 'messageMediaVideo',
            $media instanceof Audio => 'messageMediaAudio',
            $media instanceof Voice => 'messageMediaVoice',
            default => 'messageMediaUnsupported',
        };
    }
}
