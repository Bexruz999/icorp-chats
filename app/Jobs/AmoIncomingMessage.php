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
        usleep(500000);
        Log::debug('cacher:' . Cache::get("amocrm_2092452523-{$this->message['id']}"));
        sleep(1);
        Log::debug('cacher2:' . Cache::get("test123456"));
        $amo = new AmoChatService($this->connect);
        Log::debug('This: ' . json_encode([$this->contact, $this->message, $this->connect]));
        $amo->sendMessage(contact: $this->contact, msg: $this->message);
    }
}
