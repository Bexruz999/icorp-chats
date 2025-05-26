<?php

namespace App\Jobs;

use App\Services\AmoChatService;
use danog\MadelineProto\EventHandler\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

class AmoIncomingMessage implements ShouldQueue
{
    use Queueable, SerializesModels;
    private array $message;
    private array $contact;
    private array|null|string $in;

    /**
     * Create a new job instance.
     */
    public function __construct(array $message, array $user, $in = null)
    {
        $this->message = $message;
        $this->contact = ['id' => $message['chatId'], 'name' => $user['first_name'], 'phone' => $user['phone']];
        $this->in = $in;
    }

    /**
     * Execute the job.
     */
    public function handle(AmoChatService $amo): void
    {
        $amo->sendMessage(contact: $this->contact, msg: $this->message, sender: $this->in);
    }
}
