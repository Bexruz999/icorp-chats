<?php

namespace App\Listeners;

use App\Events\SendAmoCrmMessage;
use App\Services\AmoChatService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendMessageToAmoCrm implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    public function handle(SendAmoCrmMessage $event): void
    {
        $data = $event->data;

        try {
            (new AmoChatService())->sendMessage(peer_id: $data['peer_id'], msg_id: $data['id'], msg: $data['msg']);
        } catch (Exception $e) {
            Log::error('Error sending a message to AmoCRM: ' . $e->getMessage());
        }
    }
}
