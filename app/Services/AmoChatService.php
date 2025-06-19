<?php

namespace App\Services;

use AmoJo\Client\AmoJoClient;
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

    public function __construct(protected array $connect)
    {
        $this->connect();
    }

    public function connect(): void
    {
        $channel = new Channel(uid: $this->connect['amo']['uid'], secretKey: $this->connect['amo']['secret_key']);
        $this->client = new AmoJoClient(channel: $channel, segment: 'ru');
        $this->client->connect(accountUid: $this->connect['amo']['amo_account_id'], title: 'My channel');
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

    public function sendMessage(array $contact, array $msg)
    {
        $amo_contact = ($this->connect['out'] ? new Receiver() : new Sender())
            ->setProfile((new UserProfile())->setPhone($contact['phone']))
            ->setId("user-" . $contact['id'])
            ->setName($contact['name'])
            ->setAvatar($this->avatar);

        $conv = $this->createChat($amo_contact, $contact['id']);

        $payload = (new Payload())->setConversation($conv)->setMessage($this->setMessage($msg));

        if ($this->connect['out']) {
            $payload->setSender((new Sender())->setRefId($this->connect['user']['amojo_id']));
            $payload->setReceiver($amo_contact);
        } else {
            $payload->setSender($amo_contact);
        }
        \Log::debug('AmoChatService $payload: ' . json_encode($payload));

        return $this->client->sendMessage($this->connect['amo']['amo_account_id'], $payload, 'test');
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
}
