<?php

namespace App\Services;

use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\AbstractResponse;
use AmoJo\DTO\MessageResponse;
use AmoJo\Models\Channel;
use AmoJo\Models\Conversation;
use AmoJo\Models\Messages\AbstractMessage;
use AmoJo\Models\Messages\FileMessage;
use AmoJo\Models\Messages\PictureMessage;
use AmoJo\Models\Messages\TextMessage;
use AmoJo\Models\Messages\VideoMessage;
use AmoJo\Models\Messages\VoiceMessage;
use AmoJo\Models\Payload;
use AmoJo\Models\Users\Receiver;
use AmoJo\Models\Users\Sender;
use AmoJo\Models\Users\ValueObject\UserProfile;

class AmoChatService
{
    private mixed $client;
    private string $avatar = 'https://picsum.photos/300/300';

    public function __construct()
    {
        $this->connect();
    }

    public function connect(): void
    {
        $channel = new Channel(uid: config('amo.id'), secretKey: config('amo.secret_key'));
        $this->client = new AmoJoClient(channel: $channel, segment: 'ru');
        $this->client->connect(accountUid: config('amo.account_id'), title: 'My channel');
    }

    public function disconnect($client): void
    {
        $client->disconnect(accountUid: config('amo.id'));
    }

    public function createChat($contact, $chat_id): Conversation
    {
        $response = $this->client->createChat(
            accountUid: config('amo.account_id'),
            conversation: (new Conversation())->setId("chat-$chat_id"),
            contact: $contact
        );

        return (new Conversation())->setId("chat-$chat_id")->setRefId($response->getConversationRefId());
    }

    public function sendMessage($contact, array $msg, $sender = null)
    {
        $this->sendTelegramMessage(json_encode($sender));
        /*$amo_contact = ($sender ? new Receiver() : new Sender())
            ->setProfile((new UserProfile())->setPhone($contact['phone']))
            ->setId("user-" . $contact['id'])
            ->setName($contact['name'])
            ->setAvatar($this->avatar);

        $conv = $this->createChat($amo_contact, $contact['id']);

        $payload = (new Payload())->setConversation($conv)->setMessage($this->setMessage($msg));

        if ($sender !== null) {
            $payload->setSender((new Sender())->setRefId($sender))->setReceiver($amo_contact);
        } else {
            $payload->setSender($amo_contact);
        }

        return $this->client->sendMessage(config('amo.account_id'), $payload, 'test');*/
    }

    private function setMessage(array $message): AbstractMessage
    {
        if (($message['media'] !== null)) {
            $newMessage = match (true) {
                $message['mediaType'] === TelegramService::PHOTO => (new PictureMessage()),
                $message['mediaType'] === TelegramService::VIDEO => (new VideoMessage()),
                $message['mediaType'] === TelegramService::AUDIO => (new VoiceMessage()),
                $message['mediaType'] === TelegramService::DOCUMENT => (new FileMessage()),
            };
            $newMessage->setFileName($message['media']['fileName'])
                ->setFileSize($message['media']['size'])
                ->setMedia(route('tg.get-media', [
                    'message_id' => $message['id'], 'phone' => $message['self_phone']
                ]));
        } else {
            $newMessage = (new TextMessage())->setText($message['message']);
        }

        $newMessage->setUid("MSG_" . $message['id'])->setText($message['message']);

        return $newMessage;
    }

    function sendTelegramMessage($message)
    {

        $token = '6650872610:AAGxd7OcYfiAlzu8FPvprIGnUvJsK6reDxc';
        $chat_id = '781366976';

        $url = "https://api.telegram.org/bot$token/sendMessage";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['chat_id' => $chat_id, 'text' => $message, 'parse_mode' => 'HTML']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_exec($ch);
        curl_close($ch);
        //sleep(0.5); // To avoid hitting Telegram API limits

    }
}
