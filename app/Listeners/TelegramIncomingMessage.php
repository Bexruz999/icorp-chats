<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use App\Jobs\AmoIncomingMessage;
use App\Models\User;
use App\Services\TelegramService;
use Arr;
use Cache;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\Message\PrivateMessage;
use danog\MadelineProto\SimpleEventHandler;
use Log;

class TelegramIncomingMessage extends SimpleEventHandler
{

    /**
     * Handle incoming messages from Telegram.
     *
     * @param Message $message The incoming message.
     * @return void
     */
    #[Handler]
    public function handleMessage(Message $message): void
    {
        //TelegramMessage::dispatch($message);
        Log::debug('TelegramIncomingMessage: ' . json_encode($message));

        if (get_class($message) === PrivateMessage::class) {

            $connections = $this->getConnections($message);
            Log::debug('TelegramIncomingMessage connections: ' . json_encode($connections));

            $msg = json_decode(json_encode($message), true);
            $fullInfo = $this->getFullInfo($message->senderId);

            if ($message->media) {
                $msg['mediaType'] = TelegramService::getTelegramMediaType($message->media);
                $msg['self_phone'] = Arr::get($this->getSelf(), 'phone', '');
            }

            AmoIncomingMessage::dispatch(message: $msg, user: $fullInfo['User'], connect: $connections);
        }
    }

    /**
     * Get the Connections for the sender of the message.
     *
     * @param Message $message The incoming message.
     * @return array|null The Connections or null if not found.
     */
    protected function getConnections(Message $message): ?array
    {
        $message->out ? $tgId = $this->getId($message->senderId) : $tgId = $this->getSelf()['id'];

        $user = Cache::remember('telegram_user_' . $tgId, 3600, function () use ($tgId) {
            return User::where('telegram_id', $tgId)->first();
        });

        return [
            'out' => $message->out,
            'telegram' => $user->telegram->toArray(),
            'amo' => $user->amo->toArray(),
            'user' => $user->toArray()
        ];
    }
}
