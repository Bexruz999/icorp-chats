<?php

namespace App\Services;

use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\AbstractResponse;
use AmoJo\DTO\MessageResponse;
use AmoJo\Models\Channel;
use AmoJo\Models\Conversation;
use AmoJo\Models\Messages\TextMessage;
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
        //$this->client = Cache::remember('AMOChat_client_1', now()->addHour(), fn() => $this->connect());
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

    public function sendMessage($contact, $msg_id, $msg, $sender = null): MessageResponse|AbstractResponse
    {
        $amo_contact = (new Receiver())
            ->setProfile((new UserProfile())->setPhone($contact['phone']))
            ->setId("user-" . $contact['id'])
            ->setName($contact['name'])
            ->setAvatar($this->avatar);

        $conv = $this->createChat($amo_contact, $contact['id']);

        $message = (new TextMessage())->setUid("MSG_$msg_id")->setText($msg);

        if ($sender !== null) {
            $amo_sender = (new Sender())
                ->setRefId('3fbb0ea8-3ee9-4018-8339-a9a298f6b6a9');
            $payload = (new Payload())
                ->setConversation($conv)
                ->setSender($amo_sender)
                ->setReceiver($amo_contact)
                ->setMessage($message);
        } else {
            $payload = (new Payload())
                ->setConversation($conv)
                ->setSender($amo_contact)
                ->setMessage($message);
        }

        return $this->client->sendMessage(
            accountUid: config('amo.account_id'),
            payload: $payload,
            externalId: 'test'
        );
    }


}
/*
 * date
 * in_array
 * count
 * array_key_exists
 * mt_rand
 * echo
 * var_dump
 *
 *
 */
