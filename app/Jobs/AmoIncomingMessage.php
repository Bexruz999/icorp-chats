<?php

namespace App\Jobs;

use App\Services\AmoChatService;
use danog\MadelineProto\EventHandler\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AmoIncomingMessage implements ShouldQueue
{
    use Queueable;
    private Message $message;
    private array $out;
    private array|null|string $in;

    /**
     * Create a new job instance.
     */
    public function __construct(Message $message, array $user, $in = null)
    {
        $this->message = $message;
        $this->out = ['id' => $message->chatId, 'name' => $user['first_name'], 'phone' => $user['phone']];
        $this->in = $in;
    }

    /**
     * Execute the job.
     */
    public function handle(AmoChatService $amo): void
    {
        $amo->sendMessage(contact: $this->out, msg: $this->message, sender: $this->in);
    }
}
