<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class TelegramMessageShipped implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;

    /**
     * Create a new event instance.
     */
    public function __construct($result, $message, $user)
    {
        $this->data = [
            'chat_id' => $result['chat_id'],
            'id' => $result['message_id'],
            'message' => $message,
            'user' => [
                'id' => $result['user_id'],
                'self' => true,
                'first_name' => $user->first_name
            ],
            'time' => Carbon::now()->timezone('+5')->format('H:i'),
            'test' => $result
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('telegram-message-shipped'),
        ];
    }
}
