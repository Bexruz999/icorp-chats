<?php

namespace App\Jobs;

use App\Services\AmoChatService;
use Arr;
use Cache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Log;

class AmoIncomingMessage implements ShouldQueue
{
    use Queueable, SerializesModels;
    private array $message;
    private array $contact;
    private array $connect;

    /**
     * Create a new job instance.
     */
    public function __construct(array $message, array $user, array $connect)
    {
        $this->message = $message;

        $this->contact = [
            'id' => $message['chatId'],
            'name' => $user['first_name'],
            'phone' => Arr::get($user, 'phone', '')
        ];

        $this->connect = $connect;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug('This: ' . json_encode([$this->contact, $this->message, $this->connect]));
        try {
            Log::debug('c:' . Cache::get("amocrm_{$this->connect['id']}-{$this->message['id']}"));
        } catch (\Throwable $e) {
            Log::debug('errorcha: '.$e->getMessage());
        }

        if (!Cache::has("amocrm_{$this->connect['id']}-{$this->message['id']}")) {
            $amo = new AmoChatService($this->connect);
            $amo->sendMessage(contact: $this->contact, msg: $this->message);
        }
    }
}
