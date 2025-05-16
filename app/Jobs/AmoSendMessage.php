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
    private array|null $in;

    /**
     * Create a new job instance.
     */
    public function __construct(Message $message, array $user, $in = null)
    {
        $this->message_id = $message->id;
        $this->message = $message->message;
        $this->out = ['id' => $message->chatId, 'name' => $user['first_name'], 'phone' => $user['phone']];
        $this->in = $in;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $amo = new AmoChatService();
        if (empty($this->in)) {
            $amo->sendInMessage(contact: $this->out, msg_id: $this->message_id, msg: $this->message);
        } else {
            $amo->sendMessage(contact: $this->out, msg_id: $this->message_id, msg: $this->message, sender: $this->in);
        }
    }
}
