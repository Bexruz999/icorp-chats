<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\TelegramMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use danog\MadelineProto\EventHandler\Attributes\Handler;
use danog\MadelineProto\EventHandler\Message;
use danog\MadelineProto\EventHandler\Plugin\RestartPlugin;
use danog\MadelineProto\EventHandler\SimpleFilter\Incoming;
use danog\MadelineProto\SimpleEventHandler;

class TelegramIncomingMessage extends SimpleEventHandler
{

    private array $dialogs = [];



    #[Handler]

    public function handleMessage(Incoming&Message $message): void
    {
        $chatId = $message->chatId ?? null;
        $text = $message->message ?? '';
        $time = date('H:i:s', $message->date ?? time());

        if ($chatId === null) {return;}

        $this->dialogs[$chatId] = [
            'peer' => $chatId,
            'unread_count' => ($this->dialogs[$chatId]['unread_count'] ?? 0) + 1,
            'top_message' => $message->id ?? null,
            'messages' => [
                'id' => $message->id ?? null,
                'message' => $text,
            ],
            'users' => [],
            'chats' => [],
        ];

        TelegramMessage::dispatch([$this->dialogs]);
    }


    public function getDialogs()
    {

        try {
            $dialogs = $this->messages->getDialogs();
            $result = [];



            // Объединяем с теми, что были получены в handleMessage()
            foreach ($this->dialogs as $chatId => $dialog) {
                // Добавляем или обновляем диалог
                $dialogs['dialogs'][] = [
                    'peer' => $dialog['peer'],
                    'unread_count' => $dialog['unread_count'],
                    'top_message' => $dialog['top_message'],
                    'messages' => [$dialog['messages']],
                    'users' => $dialog['users'],
                    'chats' => $dialog['chats'],
                ];
            }




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
                        'unread' => $unread_count,
                        'lastMessage' => $last_message,
                    ];
                }
            }


            TelegramMessage::dispatch($result);

            var_dump($result);
            return $result;
        } catch (\Throwable $e) {
            throw new \RuntimeException("Ошибка получения диалогов: " . $e->getMessage());
        } finally {
            // Уничтожение экземпляра MadelineProto
//            $MadelineProto->stop();
        }
    }

}



//final class TelegramIncomingMessage extends SimpleEventHandler
//{
////    public $messages = ;
//
//    #[Handler]
//    public function handleMessage(Incoming&Message $message): void
//    {
//        $telegramMsg = [
//            'id' => $message->chatId ?? null,
//            'last_message' => $message->message ?? '',
//            'time' => date('H:i:s', $message->date ?? time()),
//        ];
//
//        // Ограничим количество сохраненных сообщений
//        if (count($telegramMsg) > 50) {
//            array_shift($telegramMsg);
//        }
//    }
//
//    public function getLastMessages(): array
//    {
//        return $this->messages;
//    }
//}
//
//TelegramIncomingMessage::startAndLoop('bot.madeline');


//$storage = storage_path() . '/app/telegram/62 822 11915445.madeline';
//TelegramIncomingMessage::startAndLoop($storage);
