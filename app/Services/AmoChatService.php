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
use danog\MadelineProto\EventHandler\Message;

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

    public function sendMessage($contact, array $msg, $sender = null): MessageResponse|AbstractResponse
    {
        $amo_contact = ($sender ? new Receiver() : new Sender())
            ->setProfile((new UserProfile())->setPhone($contact['phone']))
            ->setId("user-" . $contact['id'])
            ->setName($contact['name'])
            ->setAvatar($this->avatar);

        $conv = $this->createChat($amo_contact, $contact['id']);

        $message = $this->setMessage($msg);

        $payload = (new Payload())->setConversation($conv)->setMessage($message);

        if ($sender !== null) {
            $payload->setSender((new Sender())->setRefId($sender))->setReceiver($amo_contact);
        } else {
            $payload->setSender($amo_contact);
        }

        return $this->client->sendMessage(
            accountUid: config('amo.account_id'), payload: $payload, externalId: 'test'
        );
    }

    private function setMessage(array $message): AbstractMessage
    {
        $a = $message;

        $medias = [
            TelegramService::PHOTO,
            TelegramService::VIDEO,
            TelegramService::AUDIO,
            TelegramService::DOCUMENT,
            TelegramService::VOICE
        ];


        switch (true) {
            case ($message['media'] !== null) && $message['mediaType'] === $medias:
                $newMessage = (new PictureMessage());
                break;
            case ($message['media'] !== null) && $message['mediaType'] === TelegramService::VIDEO:
                $newMessage = (new VideoMessage());
                break;
            case ($message['media'] !== null) && $message['mediaType'] === TelegramService::AUDIO:
                $newMessage = (new VoiceMessage());
                break;
            case ($message['media'] !== null) && $message['mediaType'] === TelegramService::DOCUMENT:
                $newMessage = (new FileMessage());
                break;
            default:
                $newMessage = (new TextMessage())->setText($message['message']);
        }
        if ($message['media'] !== null && in_array($message['mediaType'], $medias)) {
            $newMessage->setFileName($message['media']['fileName'])
                ->setFileSize($message['media']['size'])
                ->setMedia(route('tg.get-media', [
                    'message_id' => $message['id'], 'phone' => $message['self_phone']
                ]))
                ->setText($message['message']);
        }

        $newMessage->setUid("MSG_" . $message['id']);

        return $newMessage;
    }
}
