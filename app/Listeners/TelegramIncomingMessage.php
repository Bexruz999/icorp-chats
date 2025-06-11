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
        TelegramMessage::dispatch($message);

        if (get_class($message) === PrivateMessage::class && !Cache::has(key: "amocrm_$message->chatId-$message->id")) {

            $connections = $this->getConnections($message);
            $msg =  json_decode(json_encode($message), true);
            $fullInfo = $this->getFullInfo($message->senderId);

            if ($message->media) {
                $msg['mediaType'] = TelegramService::getTelegramMediaType($message->media);
                $msg['self_phone'] = Arr::get($this->getSelf(), 'phone', '');
            }

            AmoIncomingMessage::dispatch(message: $msg, user: $fullInfo['User'], in: $connections);
        }
    }

    /**
     * Get the Connections for the sender of the message.
     *
     * @param Message $message The incoming message.
     * @return array|null The Connections or null if not found.
     */
    protected function getConnections($message): ?array
    {
        if ($message->out) {
            $tgId = $this->getId($message->senderId);

            $user = Cache::remember('telegram_user_' . $tgId, 3600, function () use ($tgId) {
                return User::where('telegram_id', $tgId)->first();
            });

            return $user ? ['telegram' => $user->telegram, 'amo' => $user->amo, 'amojo_id' => $user->amojo_id] : null;
        }

        return null;
    }
}
