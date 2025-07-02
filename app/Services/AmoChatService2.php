<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class AmoChatService2
{
    protected $client;
    protected $baseDomain;
    protected $accessToken;

    /**
     * Constructor for AmoChatService2.
     *
     * @param string $baseDomain amoCRM base domain (e.g., example.amocrm.ru)
     * @param string $accessToken amoCRM OAuth access token
     */
    public function __construct($baseDomain, $accessToken)
    {
        $this->baseDomain = $baseDomain;
        $this->accessToken = $accessToken;
        $this->client = new Client([
            'base_uri' => "https://{$baseDomain}/api/v4/",
            'timeout'  => 10.0,
        ]);
    }

    /**
     * Send a Telegram message to amoCRM chat.
     *
     * @param array $data [
     *   'chat_id' => string,
     *   'sender_id' => string,
     *   'sender_name' => string,
     *   'message_type' => string, // text, image, file, etc.
     *   'message' => string, // text or file url
     *   'caption' => string|null, // for media
     * ]
     * @return bool
     */
    public function sendTelegramMessageToAmo(array $data)
    {
        $payload = [
            'chat_id' => $data['chat_id'],
            'sender' => [
                'id' => $data['sender_id'],
                'name' => $data['sender_name'],
            ],
            'message' => [
                'type' => $data['message_type'],
                'text' => $data['message_type'] === 'text' ? $data['message'] : null,
                'media_url' => in_array($data['message_type'], ['image', 'file', 'video', 'voice']) ? $data['message'] : null,
                'caption' => $data['caption'] ?? null,
            ],
        ];

        // Remove null values
        $payload['message'] = array_filter($payload['message'], function ($v) {
            return $v !== null;
        });

        try {
            $response = $this->client->post('chats/messages', [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return $response->getStatusCode() === 200 || $response->getStatusCode() === 201;
        } catch (\Throwable $e) {
            Log::error('Failed to send Telegram message to amoCRM: ' . $e->getMessage(), [
                'exception' => $e,
                'payload' => $payload,
            ]);
            return false;
        }
    }
}
