<?php
namespace App\Services;

use App\Events\TelegramMessage;
use danog\MadelineProto\API;
use Illuminate\Support\Facades\File;
use App\Events\DialogsUpdated;


class TelegramService {

    public static function createMadelineProto(string $phone): \danog\MadelineProto\API {
        $settings = (new \danog\MadelineProto\Settings\AppInfo)
            ->setApiId(intval(env("TELEGRAM_API_ID")))
            ->setApiHash(env('TELEGRAM_API_HASH'));

        $storagePath = storage_path("app/telegram/{$phone}.madeline");

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


            TelegramMessage::dispatch($result);

            return $result;
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
            $messages = $MadelineProto->messages->getHistory([
                'peer' => $peerId,
                'limit' => 100,
            ]);

            // Проверяем, есть ли сообщения в массиве
            $messageList = $messages['messages'] ?? [];
            if (empty($messageList)) {
                return ['error' => 'Нет сообщений'];
            }

            // Определяем идентификаторы пользователей и их данные
            $selfId = null;
            $selfName = 'Unknown';
            $otherUserId = null;

            foreach ($messages['users'] as $user) {
                if (!empty($user['self'])) {
                    $selfId = $user['id'];
                    $selfName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Unknown';
                } else {
                    $otherUserId = $user['id'];
                }
            }

            // Обрабатываем сообщения
            $result = array_map(function ($message) use ($selfId, $selfName, $otherUserId, $messages) {
                $users = $messages['users'] ?? [];
                $chats = $messages['chats'] ?? [];
                $senderName = 'Unknown';
                $isSelf = false;

                if (!empty($chats)) {
                    // Групповая переписка
                    if (isset($message['from_id'])) {
                        foreach ($users as $user) {
                            if ($user['id'] === $message['from_id']) {
                                $senderName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: 'Unknown';
                                if ($user['self'] == 1) {
                                    $isSelf = true;
                                }
                                break;
                            }
                        }
                        foreach ($chats as $chat) {
                            if ($chat['id'] === $message['from_id']) {
                                $senderName = $chat['title'] ?? 'Unknown';
                                if ($user['self'] == 1) {
                                    $isSelf = true;
                                }
                                break;
                            }
                        }
                    }
                } elseif (!empty($users)) {
                    // Личная переписка
                    if (isset($message['from_id'])) {
                        if ($message['from_id'] == $selfId) {
                            $senderName = $selfName; // Имя текущего пользователя
                            $isSelf = true;
                        } elseif ($message['from_id'] == $otherUserId) {
                            // Определяем имя другого пользователя
                            foreach ($users as $user) {
                                if ($user['id'] === $otherUserId) {
                                    $firstName = $user['first_name'] ?? '';
                                    $lastName = $user['last_name'] ?? '';
                                    $senderName = trim("{$firstName} {$lastName}") ?: 'Unknown';
                                    break;
                                }
                            }
                        }
                    } elseif (empty($message['out'])) {
                        // Сообщения от другого пользователя, если `from_id` отсутствует
                        foreach ($users as $user) {
                            if ($user['id'] === $otherUserId) {
                                $firstName = $user['first_name'] ?? '';
                                $lastName = $user['last_name'] ?? '';
                                $senderName = trim("{$firstName} {$lastName}") ?: 'Unknown';

                                break;
                            }
                        }
                    }
                }

                return [
                    'id' => $message['id'] ?? null,
                    'sender' => $senderName,
                    'content' => $message['message'] ?? '',
                    'time' => isset($message['date']) ? date('H:i', $message['date']) : '',
                    'is_self' => $isSelf, // Новое поле, чтобы указать, что это сообщение отправлено текущим пользователем
                ];
            }, $messageList);

            // Сортируем сообщения по ID
            usort($result, function ($a, $b) {
                return $a['id'] <=> $b['id'];
            });

            TelegramMessage::dispatch($result);

//            event(new TelegramMessage(end($result))); // Отправляем последнее сообщение в канал

            return $result;
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










