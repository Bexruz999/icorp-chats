<?php

namespace App\Services;

use App\Models\UserMessage;
use Arr;
use danog\MadelineProto\API;
use danog\MadelineProto\Exception;
use danog\MadelineProto\Settings\AppInfo;
use Illuminate\Http\UploadedFile;
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


    /**
     * Retrieve a list of dialogs (chats, groups, channels) for a given phone number.
     *
     * This function fetches the user's dialogs via MadelineProto, returning chat information.
     * It can be useful for listing available chats to choose where to send messages or media.
     *
     * @param string $phone The phone number associated with the Telegram account.
     *
     * @return array The list of dialogs, including chat IDs, names, and types.
     *
     * @throws \Exception If fetching dialogs fails or the API returns an error.
     */
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
        try {

            $MadelineProto = self::createMadelineProto($phone);

            $messages = $MadelineProto->messages->getHistory(['peer' => $peerId, 'limit' => 100]);
            $collect = collect($messages['messages'])->sortBy('id');
            $usersCollect = collect($messages['users']);
            $userMessages = UserMessage::with('user')->where('chat_id', $peerId)->get();

            $result = [];
            $select = ['id', 'self', 'first_name', 'last_name', 'phone'];
            foreach ($collect->where('_', 'message') as $message) {

                $from_id = array_key_exists('from_id', $message) ? $message['from_id'] : $message['peer_id'];

                $media = Arr::get($message, 'media', false);

                $msg_data = [
                    'id'     => $message['id'],
                    'user'   => $usersCollect->select($select)->where('id', $from_id)->first(),
                    'message'=> $message['message'],
                    'time'   => Carbon::parse($message['date'])->timezone('+5')->format('H:i'),
                ];

                if ($media) {
                    $msg_data['media'] = $media;
                    if ($media['_'] === 'messageMediaDocument') {
                        $msg_data['media']['file_name'] = collect(Arr::get($media, 'document.attributes'))->where('_', 'documentAttributeFilename')->value('file_name');
                    }
                };

                $result[] = $msg_data;
            }

            return $result;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    /**
     *  Send a message to a specified chat ID via Telegram.
     *
     *  This function prepares and sends a message to a Telegram chat using MadelineProto.
     *
     * @param string $phone
     * @param int $peerId
     * @param string $message
     * @return array
     */
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


    /**
     * Send a media file to a specified chat ID via Telegram.
     *
     * This function handles sending various types of media (e.g., photo, video, document)
     * to a Telegram chat using MadelineProto. It also supports adding an optional caption.
     *
     * @param string $mediaType The type of media to send (e.g., 'photo', 'video', 'document').
     * @param int $chatId The ID of the chat to send the media to.
     * @param string $uploadPath The file path where the media is stored on the server.
     * @param string $fileName The name of the file being sent.
     * @param string|null $message (Optional) A caption or message to send with the media.
     *
     * @return void
     */
    public function sendMedia(
        string $mediaType,
        int $chatId,
        string $uploadPath,
        string $fileName,
        ?string $message = ''
    ): void
    {
        $user = auth()->user();
        $phone = $user->account->connections[0]->phone;

        $MadelineProto = self::createMadelineProto($phone);

        $uploadedFile = $MadelineProto->upload($uploadPath);

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


    /**
     * Determine the appropriate media type for MadelineProto based on the file extension.
     *
     * This function maps common file extensions to the corresponding MadelineProto media type.
     * It helps automatically choose the correct media type when sending files via Telegram.
     *
     * @param UploadedFile $file The file name or path.
     *
     * @return string The media type for MadelineProto (e.g., 'photo', 'video', 'document', 'audio').
     */
    function getMediaTypeForMadelineProto(UploadedFile $file): string
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

    public function getMedia($message_id) {
        $user = auth()->user();
        $phone = $user->account->connections[0]->phone;

        $MadelineProto = self::createMadelineProto($phone);

        $message = $MadelineProto->messages->getMessages(['id' => [$message_id]]);

        if ($message['messages'][0]['_'] !== 'messageEmpty') {
            $media = $message['messages'][0]['media'];

            $MadelineProto->downloadToBrowser($media);
        } else {
            abort(404);
        }

    }
}
