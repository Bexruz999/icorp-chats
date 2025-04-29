<?php

namespace App\Services;

use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\AbstractResponse;
use AmoJo\DTO\ConnectResponse;
use AmoJo\DTO\MessageResponse;
use AmoJo\Models\Channel;
use AmoJo\Models\Conversation;
use AmoJo\Models\Messages\TextMessage;
use AmoJo\Models\Payload;
use AmoJo\Models\Users\Sender;
use AmoJo\Models\Users\ValueObject\UserProfile;
use Cache;

class AmoChatService
{
    private ConnectResponse|AbstractResponse $client;
    private string $avatar = 'https://picsum.photos/300/300';

    public function __construct()
    {
        $this->client = Cache::remember('AMOChat_client_1', now()->addHour(), fn() => $this->connect());
    }

    public function connect(): ConnectResponse|AbstractResponse
    {
        $channel = new Channel(uid: config('amo.id'), secretKey: config('amo.secret_key'));
        $client = new AmoJoClient(channel: $channel, segment: 'ru');
        return $client->connect(accountUid: config('amo.account_id'), title: 'My channel');
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

    public function sendMessage($peer_id, $msg_id, $msg): MessageResponse|AbstractResponse
    {
        $contact = (new Sender())
            ->setAvatar($this->avatar)
            ->setId("user-$peer_id")
            ->setName('Ivan Ivanov')
            ->setProfile((new UserProfile())->setPhone('+1464874556719'));

        $conv = $this->createChat($contact, $peer_id);

        $message = (new TextMessage())->setUid("MSG_$msg_id")->setText($msg);

        return $this->client->sendMessage(
            accountUid: config('amo.account_id'),
            payload: (new Payload())->setConversation($conv)->setSender($contact)->setMessage($message),
            externalId: 'test'
        );
    }
}
