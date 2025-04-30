<?php

namespace App\Events;

use App\Services\AmoChatService;
use danog\MadelineProto\EventHandler\Media\Audio;
use danog\MadelineProto\EventHandler\Media\Document;
use danog\MadelineProto\EventHandler\Media\Photo;
use danog\MadelineProto\EventHandler\Media\Video;
use danog\MadelineProto\EventHandler\Media\Voice;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\Message\GroupMessage;
use Exception;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Log;

class TelegramMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        try {
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

            (new AmoChatService())->sendMessage(
                peer_id: $result['chat_id'],
                msg_id: $result['id'],
                msg: $result['message']
            );
            $this->message = $result;

        } catch (Exception $e) {
            Log::error('Xabarni qayta ishlashda xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('telegram-messages'),
        ];
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
