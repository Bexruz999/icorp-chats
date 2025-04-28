<?php

namespace App\Services;

use AmoJo\Client\AmoJoClient;
use AmoJo\DTO\AbstractResponse;
use AmoJo\DTO\ConnectResponse;
use AmoJo\Models\Channel;
use AmoJo\Models\Conversation;
use AmoJo\Models\Messages\TextMessage;
use AmoJo\Models\Payload;
use AmoJo\Models\Users\Receiver;
use AmoJo\Models\Users\Sender;
use AmoJo\Models\Users\ValueObject\UserProfile;

class AmoChatService
{

    private AmoJoClient $client;

    public function connect(): ConnectResponse|AbstractResponse
    {

        $channel = new Channel(uid: '25dd1cbb-a999-4cb3-a34d-09b0431ff2b8', secretKey: '7b5e3cc7a7137e2638647d6c40290f05aa84c69a');
        $this->client = new AmoJoClient(channel: $channel, additionalMiddleware: [], segment: 'ru');

        $response = $this->client->connect(accountUid: config('amo.account_id'), title: 'My channel');

        echo 'Scope ID: ' . $response->getScopeId(); //25dd1cbb-a999-4cb3-a34d-09b0431ff2b8_00db92bc-8371-4adc-9092-9cba4a3654faChat

        return $response;
    }

    public function disconnect(): void
    {
        $response = $this->client->disconnect(accountUid: config('amo.id'));
        if ($response->getDisconnect()) {echo 'The channel has been successfully disabled';}
    }

    public function createChat(): array
    {
        $conversation = (new Conversation())->setId('chat-1234');
        $contact = (new Sender())
            ->setId('user-123')
            ->setName('Ivan Ivanov')
            ->setAvatar('https://picsum.photos/302/300')
            ->setProfile((new UserProfile())->setPhone('+1464874556719'));

        $response = $this->client->createChat(
            accountUid: config('amo.account_id'),
            conversation: $conversation,
            contact: $contact
        );

        echo 'Chat ID in the Chat API: ' . $response->getConversationRefId();//2012f7b4-29a4-4425-9579-a563b9383c0b

        return [
            'refID' => $response->getConversationRefId(),
            'conversation' => $conversation,
            'contact' => $contact
        ];
    }

    public function sendMessage($data) {

        $message = (new TextMessage())->setUid('MSG_1014538')->setText('Hello');
        $conv = (new Conversation())->setId('chat-1234')->setRefId($data['refID']);
        $contact = $data['contact'];

        $response = $this->client->sendMessage(
            accountUid: config('amo.account_id'),
            payload: (new Payload())->setConversation($conv)->setSender($contact)->setMessage($message),
            externalId: 'test'
        );

        echo 'Message ID: ' . $response->getConversationRefId();
        return $response;
    }

}
