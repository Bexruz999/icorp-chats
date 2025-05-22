<?php

namespace App\Services;

use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\AbstractResponse;
use AmoJo\DTO\MessageResponse;
use AmoJo\Models\Channel;
use AmoJo\Models\Conversation;
use AmoJo\Models\Messages\AbstractMessage;
use AmoJo\Models\Messages\PictureMessage;
use AmoJo\Models\Messages\TextMessage;
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

    public function sendMessage($contact, Message $msg, $sender = null): MessageResponse|AbstractResponse
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

    private function setMessage(Message $message): AbstractMessage
    {
        switch (true){
            case ($message->media !== null):
                return (new PictureMessage())
                    ->setUid("MSG_$message->id")
                    ->setFileName($message->media->fileName)
                    ->setMimeType($message->media->mimeType)
                    ->setText($message->message);
            default:
                return (new TextMessage())->setUid("MSG_$message->id")->setText($message->message);
        }
    }
}
