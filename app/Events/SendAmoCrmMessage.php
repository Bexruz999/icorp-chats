<?php

namespace App\Events;

use danog\MadelineProto\EventHandler\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendAmoCrmMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    /**
     * Create a new event instance.
     */
    public function __construct(array|Message $data)
    {
        if (is_array($data)) {
            $this->data = $data;
        } else {
            $this->data = [
                'chat_id' => $data->chatId,
                'id' => $data->id,
                'message' => $data->message,
            ];
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
            new PrivateChannel('amo-chat_send-message-channel'),
        ];
    }
}
