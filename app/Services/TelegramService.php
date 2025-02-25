<?php

namespace App\Services;

use App\Models\UserMessage;
use Arr;
use danog\MadelineProto\API;
use danog\MadelineProto\Exception;
use danog\MadelineProto\Settings\AppInfo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Storage;
use Throwable;


class TelegramService
{

    public static function createMadelineProto(string $phone): string|API
    {
        $settings = (new AppInfo)
            ->setApiId(intval(env("TELEGRAM_API_ID")))
            ->setApiHash(env('TELEGRAM_API_HASH'));

        $storagePath = storage_path("app/telegram/$phone.madeline");

        try {
            return new API($storagePath, $settings);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getDialogs(string $phone): array
    {
        $MadelineProto = self::createMadelineProto($phone);

        try {

            $limit = 100; // Number of dialogs to fetch per page
            $date = 0; // Start point (for the first request, it's 0)
            $dialogsCollect = [];

            while (true) {
                // Get dialogs with pagination
                $dialogs = $MadelineProto->messages->getDialogs(offset_date: $date, limit: $limit);

                // Check if we received any dialogs
                if (count($dialogs['dialogs']) == 0) {
                    break;
                }

                $users = collect($dialogs['users'])->where('bot', false);
                $chats = collect($dialogs['chats'])->whereIn('_', ['channel', 'chat']);
                $messages = collect($dialogs['messages']);

                // Merge the dialogs with the existing ones
                foreach ($dialogs['dialogs'] as $dialog) {

                    $user = $users->where('id', $dialog['peer'])->first();
                    $chat = $chats->where('id', $dialog['peer'])->first();
                    $message = $messages->where('peer_id', $dialog['peer'])
                        ->where('id', $dialog['top_message'])->first();
                    if ($chat) {
                        $title = Arr::get($chat, 'title');
                        $type = 'chat';
                    } else if ($user) {
                        $title = Arr::get($user, 'first_name');
                        $type = 'user';
                    } else {
                        // Skip non-existing users and channels
                        continue;
                    }

                    $dialogsCollect[] = [
                        'last_message' => Str::limit(Arr::get($message, 'message', 'no message'), 30),
                        'peer_id' => $dialog['peer'],
                        'title' => $title,
                        'type' => $type,
                        'unread_count' => $dialog['unread_count'],
                        'time' => Carbon::createFromTimestamp(Arr::get($message, 'date'))->format('Y-m-d H:i:s')
                    ];
                }

                // Update the date to get the next set of dialogs
                $date = (int)collect($dialogs['messages'])->sortBy('date')->first()['date'];
            }

            return $dialogsCollect;
        } catch (Throwable $e) {
            throw new RuntimeException("Ошибка получения диалогов: " . $e->getMessage());
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

    public function sendMessage(string $phone, int $peerId, string $message): array
    {
        $MadelineProto = self::createMadelineProto($phone);

        try {
            $result = $MadelineProto->messages->sendMessage(peer: $peerId, message: $message);

            return ['success' => true, 'message_id' => $result['id'] ?? null];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public static function getStoragePath(string $phone, string $path = 'app/telegram/'): string
    {
        $path = storage_path($path);

        if (!File::exists($path)) {
            File::makeDirectory($path, 0777, true, true);
        }

        return "$path$phone.madeline";
    }

    public function sendMedia(string $mediaType, int $chatId, string $uploadPath, string $fileName, ?string $message = ''): void
    {
        $user = auth()->user();
        $phone = $user->account->connections[0]->phone;

        $MadelineProto = self::createMadelineProto($phone);

        $uploadedFile = $MadelineProto->upload(storage_path('app/public/' . $uploadPath));

        if (!isset($_SESSION['grouped_id'])) {
            $_SESSION['grouped_id'] = mt_rand(1000000, 9999999);
        }

        $MadelineProto->messages->sendMultiMedia(
            peer: $chatId,
            multi_media: [
                [
                    '_' => 'inputSingleMedia',
                    'media' => [
                        '_' => $mediaType,
                        'file' => $uploadedFile,
                        'attributes' => [[
                                '_' => 'documentAttributeFilename',
                                'file_name' => $fileName,
                            ]],
                    ],
                    'message' => $message,
                    'grouped_id' => $_SESSION['grouped_id'],
                ]
            ]);

        Storage::delete($uploadPath);
    }

    function getMediaTypeForMadelineProto($file)
    {
        $extension = $file->getClientOriginalExtension();

        $mediaTypes = [
            'jpg' => 'inputMediaUploadedPhoto',
            'jpeg' => 'inputMediaUploadedPhoto',
            'png' => 'inputMediaUploadedPhoto',
            'gif' => 'inputMediaUploadedPhoto',
            'mp4' => 'inputMediaUploadedDocument',
            'mov' => 'inputMediaUploadedDocument',
            'avi' => 'inputMediaUploadedDocument',
            'mp3' => 'inputMediaUploadedDocument',
            'ogg' => 'inputMediaUploadedDocument',
            'pdf' => 'inputMediaUploadedDocument',
            'zip' => 'inputMediaUploadedDocument',
        ];

        return $mediaTypes[$extension] ?? 'inputMediaUploadedDocument';
    }

}
