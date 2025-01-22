<?php
namespace App\Services;

use danog\MadelineProto\API;
use Illuminate\Support\Facades\File;
use function Amp\File\write;


class TelegramService {

    public static function createMadelineProto(string $phone): \danog\MadelineProto\API {
        $settings = (new \danog\MadelineProto\Settings\AppInfo)
            ->setApiId(intval(env("TELEGRAM_API_ID")))
            ->setApiHash(env('TELEGRAM_API_HASH'));

        $storagePath = self::getStoragePath($phone, 'app/telegram/');

        return new \danog\MadelineProto\API($storagePath, $settings);
    }

    public function getDialogs(string $phone)
    {
        $MadelineProto = self::createMadelineProto($phone);

        try {
            $dialogs = $MadelineProto->messages->getDialogs(limit: 100);
            $result = [];


            if (isset($dialogs['dialogs']) && is_array($dialogs['dialogs'])) {
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
            }

            return $result;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
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
            $messages = $MadelineProto->messages->getHistory([
                'peer' => $peerId,
                'limit' => 100,
            ]);

            return array_map(function ($message) {
                return [
                    'id' => $message['id'] ?? null,
                    'sender' => $message['from_id']['user_id'] ?? 'Unknown',
                    'content' => $message['message'] ?? '',
                    'time' => isset($message['date']) ? date('H:i', $message['date']) : '',
                ];
            }, $messages['messages'] ?? []);
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
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










