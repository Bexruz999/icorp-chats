<?php
namespace App\Services;

use App\Events\TelegramMessage;
use App\Models\UserMessage;
use Arr;
use danog\MadelineProto\API;
use danog\MadelineProto\Settings\AppInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use App\Events\DialogsUpdated;
use Illuminate\Support\Str;


class TelegramService {

    public static function createMadelineProto(string $phone): \danog\MadelineProto\API {
        $settings = (new AppInfo)
            ->setApiId(intval(env("TELEGRAM_API_ID")))
            ->setApiHash(env('TELEGRAM_API_HASH'));

        $storagePath = storage_path("app/telegram/{$phone}.madeline");

        return new API($storagePath, $settings);
    }

    public function getDialogs(string $phone)
    {
        $MadelineProto = new API(storage_path("app/telegram/{$phone}.madeline"));

        try {

            $limit = 100; // Number of dialogs to fetch per page
            $date = 0; // Start point (for the first request, it's 0)
            $dialogsCollect = [];

            while (true) {
                // Get dialogs with pagination
                $dialogs = $MadelineProto->messages->getDialogs(offset_date: $date, limit: $limit);

                // Check if we received any dialogs
                if (count($dialogs['dialogs']) == 0) {break;}

                $users = collect($dialogs['users'])->select(['id', 'bot', 'first_name'])->where('bot', false);
                $chats = collect($dialogs['chats'])->where('_', 'channel');
                $messages = collect($dialogs['messages']);

                // Merge the dialogs with the existing ones
                foreach ($dialogs['dialogs'] as $dialog) {

                    $user = $users->where('id', $dialog['peer'])->first();
                    $chat = $chats->where('id', $dialog['peer'])->first();
                    $message = $messages->where('peer_id', $dialog['peer'])
                        ->where('id', $dialog['top_message'])->first();
                    if ($chat) {
                        $title = Arr::get($chat,'title');
                        $type = 'chat';
                    } else {
                        $title = Arr::get($user, 'first_name');
                        $type = 'user';
                    }

                    $dialogsCollect[] = [
                        'peer_id' => $dialog['peer'],
                        'title' => $title,
                        'type' => $type,
                        'last_message' => Str::limit(Arr::get($message, 'message', 'no message'), 30),
                        'unread_count' => $dialog['unread_count'],
                        'time' => Carbon::createFromTimestamp(Arr::get($message, 'date'))->format('Y-m-d H:i:s')
                    ];
                }

                // Update the date to get the next set of dialogs
                $date = (int)collect($dialogs['messages'])->sortBy('date')->first()['date'];
                break;
            }

            /*if (isset($dialogs['dialogs']) && is_array($dialogs['dialogs'])) {
                foreach ($dialogs['dialogs'] as $dialog) {
                    if (!is_array($dialog)) {
                        continue;
                    }

                    $peer_id = $dialog['peer'] ?? null;
                    $unread_count = $dialog['unread_count'] ?? 0;
                    $top_message_id = $dialog['top_message'] ?? null;
                    $title = 'Unknown';
                    $type = 'unknown';
                    $last_message = null;

                    if (is_numeric($peer_id)) {
                        if ($peer_id > 0) {
                            $type = 'user';
                            // Исключаем ботов
                            $user = array_filter($dialogs['users'], function($u) use ($peer_id) {
                                return $u['id'] === $peer_id && $u['bot'] != 1 && $u['support'] != 1;
                            });
                            $user = reset($user);
                            if ($user) {
                                $title = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                            } else {
                                continue; // Пропускаем диалоги с ботами
                            }
                        } else {
                            $type = 'chat';
                            $chat_id = $peer_id;
                            // Исключаем каналы
                            $chat = array_filter($dialogs['chats'], fn($c) => $c['id'] === $chat_id && $c['_'] !== 'channel');
                            $chat = reset($chat);
                            if ($chat) {
                                $title = $chat['title'] ?? 'Unknown Chat';
                            } else {
                                continue; // Пропускаем каналы
                            }
                        }
                    }

                    // Последнее сообщение
                    if ($top_message_id) {
                        $message = array_filter($dialogs['messages'], fn($m) => $m['id'] === $top_message_id);
                        $message = reset($message);
                        if ($message) {
                            $last_message = $message['message'] ?? 'No message';
                        }
                    }

                    $result[] = [
                        "peer_id" => $peer_id,
                        'type' => $type,
                        'title' => $title,
                        'unread_count' => $unread_count,
                        'last_message' => $last_message,
                    ];
                }
            }*/

            return $dialogsCollect;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Ошибка получения диалогов: " . $e->getMessage());
        } finally {
            // Уничтожение экземпляра MadelineProto
//            $MadelineProto->stop();
        }
    }


    /**
     * Получение последних сообщений для выбранного диалога.
     *
     * @param string $phone
     * @param int $peerId
     * @return array
     */
    public function getMessages(string $phone, int $peerId): array
    {
        $MadelineProto = self::createMadelineProto($phone);

        try {
            $messages = $MadelineProto->messages->getHistory(['peer' => $peerId, 'limit' => 100]);

            $collect = collect($messages['messages'])->sortBy('id');
            $usersCollect = collect($messages['users']);

            $userMessages = UserMessage::with('user')->where('chat_id', $peerId)->get();

            $test = [];
            $select = ['id', 'self', 'first_name', 'last_name', 'phone'];
            foreach ($collect->where('_', 'message') as $message) {

                $from_id = array_key_exists('from_id', $message) ? $message['from_id'] : $message['peer_id'];
                $sender = $userMessages->where('message_id', $message['id'])->first();
                $test[] = [
                    'id' => $message,
                    'user' => $usersCollect->select($select)->where('id', $from_id)->first(),
                    'message' => $message['message'],
                    'sender' => $sender->user->first_name ?? false,
                    //'fwd_from' => array_key_exists('fwd_from', $message) ? $message['fwd_from'] : false,
                ];
            }

            return $test;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


//    use App\Events\TelegramMessage;




    public function sendMessage(string $phone, int $peerId, string $message): array
    {
        $MadelineProto = self::createMadelineProto($phone);

        try {
            $result = $MadelineProto->messages->sendMessage([
                'peer' => $peerId,
                'message' => $message,
            ]);

            return ['success' => true, 'message_id' => $result['id'] ?? null];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    public static function getStoragePath(string $phone, string $path = 'app/telegram/') {
        $path = storage_path($path);

        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return "{$path}{$phone}.madeline";
    }

}
