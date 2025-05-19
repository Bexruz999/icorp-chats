<?php

namespace App\Jobs;

use App\Services\AmoChatService;
use danog\MadelineProto\EventHandler\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AmoSendMessage implements ShouldQueue
{
    use Queueable;

    private int $message_id;
    private string $message;
    private array $out;
    private array|null|string $sender = null;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $result = $data['result'];
        $chat = $data['chat'];

        $this->message_id = $result['id'];
        $this->message = $result['request']['body']['message'];
        $this->out = ['id' => $chat['id'], 'name' => $chat['first_name'], 'phone' => $chat['phone']];
        $this->sender = auth()->user()->amojo_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $amo = new AmoChatService();
        $amo->sendMessage(contact: $this->out, msg_id: $this->message_id, msg: $this->message, sender: $this->sender);
    }
}
